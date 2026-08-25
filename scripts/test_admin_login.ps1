$u='http://localhost/ecommerce/admin/login.php'
$sess=New-Object Microsoft.PowerShell.Commands.WebRequestSession
$r=Invoke-WebRequest -Uri $u -WebSession $sess -UseBasicParsing
Write-Output '--- GET /admin/login.php ---'
Write-Output ('Status: ' + $r.StatusCode)
Write-Output 'Cookies:'
$cookie = $sess.Cookies.GetCookies($u)
foreach ($c in $cookie) { Write-Output ("  {0}={1}" -f $c.Name, $c.Value) }
$post = @{email='admin@example.com'; mot_de_passe='secret'; redirect='/ecommerce/admin/dashboard.php'}
$rp = Invoke-WebRequest -Uri $u -Method POST -Body $post -WebSession $sess -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
Write-Output '--- POST /admin/login.php ---'
if ($rp) { Write-Output ('Status: ' + $rp.StatusCode) } else { Write-Output 'No direct response (likely 302)' }
Write-Output 'ResponseHeaders:'
if ($rp) { foreach ($h in $rp.Headers.GetEnumerator()) { Write-Output ("  {0}: {1}" -f $h.Name, $h.Value) } }
if ($rp) {
	Write-Output ('Content length: ' + ($rp.RawContentLength))
	$snippet = $rp.Content.Substring(0,[Math]::Min(512,$rp.Content.Length))
	Write-Output 'Body snippet:'
	Write-Output $snippet
}
Write-Output 'Session Cookies after POST:'
$cookie = $sess.Cookies.GetCookies($u)
foreach ($c in $cookie) { Write-Output ("  {0}={1}" -f $c.Name, $c.Value) }
$d='http://localhost/ecommerce/admin/dashboard.php'
$rd=Invoke-WebRequest -Uri $d -WebSession $sess -UseBasicParsing -ErrorAction SilentlyContinue
Write-Output '--- GET /admin/dashboard.php ---'
if ($rd) {
	Write-Output ('Status: ' + $rd.StatusCode)
	$len = [Math]::Min(1200, $rd.Content.Length)
	Write-Output ('Content snippet (first ' + $len + ' chars):')
	Write-Output $rd.Content.Substring(0, $len)
} else { Write-Output 'No response from dashboard GET' }
