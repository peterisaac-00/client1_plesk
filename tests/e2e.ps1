$ErrorActionPreference = "Stop"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$OutputEncoding = [System.Text.Encoding]::UTF8
$base = "http://127.0.0.1:8080"
$jar = "C:\Users\Peto\AppData\Local\Temp\opencode\cookies.txt"
Remove-Item $jar -Force -ErrorAction SilentlyContinue
$tmp = "C:\Users\Peto\AppData\Local\Temp\opencode"
$pass = 0; $fail = 0

# إعادة ضبط قاعدة البيانات قبل الاختبار (عبر PDO لأن بنية MariaDB لا تتضمن عميل SQL)
& "C:\Users\Peto\AppData\Local\Temp\opencode\php\php.exe" "D:\client1\tests\reset_db.php" | Out-Null

function CurlReq([string[]]$args2) {
    $out = & curl.exe @args2 2>&1
    return ($out | Out-String).Trim()
}
function Get-Csrf([string]$html) {
    if ($html -match 'name="_csrf" value="([^"]+)"') { return $matches[1] }
    return ""
}
function Check([string]$name, [bool]$ok, [string]$extra = "") {
    if ($ok) { $script:pass++; Write-Host "PASS: $name" -ForegroundColor Green }
    else { $script:fail++; Write-Host "FAIL: $name  $extra" -ForegroundColor Red }
}

# 1. صفحة تسجيل الدخول
$r = CurlReq @("-s","-c",$jar,"-b",$jar,"$base/login")
Check "login page 200" ($r -match "تسجيل الدخول")
$csrf = Get-Csrf $r

# 2. تسجيل دخول خاطئ
$r2 = CurlReq @("-s","-c",$jar,"-b",$jar,"-L","-d","_csrf=$csrf&email=admin@example.com&password=wrongpass","$base/login")
Check "wrong password rejected" ($r2 -match "بيانات الدخول غير صحيحة")

# 3. تسجيل دخول صحيح -> إجبار تغيير كلمة المرور
$r = CurlReq @("-s","-c",$jar,"-b",$jar,"-L","-d","_csrf=$csrf&email=admin@example.com&password=Admin@12345","$base/login")
Check "login success -> change password forced" ($r -match "تغيير كلمة المرور الافتراضية")

# 4. تغيير كلمة المرور
$csrf = Get-Csrf $r
$r = CurlReq @("-s","-c",$jar,"-b",$jar,"-L","-d","_csrf=$csrf&current_password=Admin@12345&new_password=NewPass!2026&confirm_password=NewPass!2026","$base/admin/change-password")
Check "password changed -> dashboard" ($r -match "لوحة التحكم")

# 5. لوحة التحكم
Check "dashboard stats visible" ($r -match "إجمالي المستندات" -and $r -match "عمليات التحقق")

# 6. إضافة عميل
$r = CurlReq @("-s","-c",$jar,"-b",$jar,"-L","-d","_csrf=$csrf&name=شركة الاختبار&phone=0555555555&email=test@company.com&notes=عميل تجريبي","$base/admin/clients/store")
Check "client created" ($r -match "شركة الاختبار")

# 7. رفع مستند PDF
$pdf = "$tmp\test-doc.pdf"
$pdfBytes = [System.Text.Encoding]::ASCII.GetBytes("%PDF-1.4`n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj`n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj`n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 200 200]>>endobj`ntrailer<</Root 1 0 R>>`n%%EOF")
[System.IO.File]::WriteAllBytes($pdf, $pdfBytes)
$r = CurlReq @("-s","-c",$jar,"-b",$jar,"-L","-F","_csrf=$csrf","-F","client_id=1","-F","document=@$pdf;type=application/pdf","$base/admin/documents/store")
Check "document uploaded" ($r -match "DOC-2026-00001")

# 8. استخراج id المستند ثم رمز التحقق من صفحة QR
$r = CurlReq @("-s","-c",$jar,"-b",$jar,"$base/admin/documents")
$docId = ""
if ($r -match "/admin/documents/(\d+)/qr") { $docId = $matches[1] }
$docNumber = ""
if ($r -match "DOC-2026-(\d{5})") { $docNumber = "DOC-2026-" + $matches[1] }
$r = CurlReq @("-s","-c",$jar,"-b",$jar,"$base/admin/documents/$docId/qr")
$token = ""
if ($r -match "/verify/([a-f0-9]{32})") { $token = $matches[1] }
Check "qr generated automatically (token found)" ($token -ne "")

# 9. تفعيل QR
$r = CurlReq @("-s","-c",$jar,"-b",$jar,"-L","-d","_csrf=$csrf","$base/admin/documents/$docId/activate")
Check "qr activated" ($r -match "تم تفعيل رمز QR")

# 10. صفحة التحقق العامة (مفعل)
$r = CurlReq @("-s","-b",$jar,"$base/verify/$token")
Check "verify page shows verified document" ($r -match "مستند موثق" -and $r -match $docNumber)

# 11. عرض الملف من صفحة التحقق
$code = CurlReq @("-s","-o","$tmp\downloaded.pdf","-w","%{http_code}","-b",$jar,"$base/verify/$token/file")
$first = Get-Content "$tmp\downloaded.pdf" -TotalCount 1
Check "file served publicly (200)" ($code -eq "200")
Check "file is pdf" ($first -match "%PDF")

# 12. سجل التحقق
$r = CurlReq @("-s","-c",$jar,"-b",$jar,"$base/admin/verifications")
Check "verification logged" ($r -match $docNumber)

# 13. تعطيل QR
$r = CurlReq @("-s","-c",$jar,"-b",$jar,"-L","-d","_csrf=$csrf","$base/admin/documents/$docId/disable")
Check "qr disabled" ($r -match "تم تعطيل رمز QR")
$r = CurlReq @("-s","-b",$jar,"$base/verify/$token")
Check "verify shows unavailable after disable" ($r -match "غير متاح للتحقق")

# 14. Token غير موجود
$code = CurlReq @("-s","-o","$tmp\bad.html","-w","%{http_code}","-b",$jar,"$base/verify/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa")
$bad = Get-Content "$tmp\bad.html" -Raw -Encoding UTF8
Check "invalid token 404" ($code -eq "404" -and $bad -match "المستند غير موجود")

# 15. حذف المستند
$r = CurlReq @("-s","-c",$jar,"-b",$jar,"-L","-d","_csrf=$csrf","$base/admin/documents/$docId/delete")
Check "document deleted" ($r -match "تم حذف المستند")

# 16. تسجيل خروج وحماية الصفحات
$r = CurlReq @("-s","-c",$jar,"-b",$jar,"-L","-d","_csrf=$csrf","$base/logout")
$r2 = CurlReq @("-s","-c",$jar,"-b",$jar,"-L","$base/admin")
Check "admin protected after logout" ($r2 -match "تسجيل الدخول")

Write-Host "==================================="
Write-Host "PASSED: $pass  FAILED: $fail"
if ($fail -gt 0) { exit 1 } else { exit 0 }