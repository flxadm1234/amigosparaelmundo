# Setup Firewall AMIGOS POR EL MUNDO PHP LAN 8000
# Requiere Admin: se ejecuta auto-elevado via .bat si fltmc detecta usuario normal
$RuleName = "AMIGOS POR EL MUNDO PHP LAN 8000"
try {
    $existing = Get-NetFirewallRule -DisplayName $RuleName -ErrorAction SilentlyContinue
    if (-not $existing) {
        New-NetFirewallRule -DisplayName $RuleName `
            -Direction Inbound -Protocol TCP -LocalPort 8000 `
            -Action Allow -Profile Any -EdgeTraversalPolicy Allow `
            -RemoteAddress Any -Program $null `
            -Group "AMIGOS TV" -ErrorAction Stop | Out-Null
        Write-Host "[OK] Regla Firewall CREADA: $RuleName (TCP 8000 inbound ANY profile)" -ForegroundColor Green
    } else {
        Set-NetFirewallRule -DisplayName $RuleName -Enabled True -Direction Inbound `
            -Action Allow -Profile Any -EdgeTraversalPolicy Allow -RemoteAddress Any -ErrorAction Stop
        Write-Host "[OK] Regla Firewall ACTUALIZADA/HABILITADA: $RuleName" -ForegroundColor Green
    }
    netsh advfirewall firewall show rule name="$RuleName" verbose
} catch {
    Write-Host "[ERROR] No se pudo configurar el firewall: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}
Write-Host "`nListo. Presiona ENTER para cerrar."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
