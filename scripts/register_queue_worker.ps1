param(
    [Parameter(Mandatory = $true)]
    [string] $PhpPath,

    [switch] $Start
)

$ErrorActionPreference = 'Stop'

$taskName = 'HRD System Queue Worker'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$workerScript = Join-Path $PSScriptRoot 'queue_worker.bat'

$principal = New-Object Security.Principal.WindowsPrincipal(
    [Security.Principal.WindowsIdentity]::GetCurrent()
)

if (-not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    throw 'Registrasi Scheduled Task harus dijalankan sebagai Administrator.'
}

if (-not (Test-Path -LiteralPath $PhpPath -PathType Leaf)) {
    throw "php.exe tidak ditemukan: $PhpPath"
}

if (-not (Test-Path -LiteralPath $workerScript -PathType Leaf)) {
    throw "Worker script tidak ditemukan: $workerScript"
}

$actionArguments = '/d /c ""{0}" "{1}""' -f $workerScript, $PhpPath
$action = New-ScheduledTaskAction `
    -Execute $env:ComSpec `
    -Argument $actionArguments `
    -WorkingDirectory $projectRoot

$trigger = New-ScheduledTaskTrigger -AtStartup

$settings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable `
    -RestartCount 999 `
    -RestartInterval (New-TimeSpan -Minutes 1) `
    -ExecutionTimeLimit ([TimeSpan]::Zero) `
    -MultipleInstances IgnoreNew

$taskPrincipal = New-ScheduledTaskPrincipal `
    -UserId 'SYSTEM' `
    -LogonType ServiceAccount `
    -RunLevel Highest

Register-ScheduledTask `
    -TaskName $taskName `
    -Action $action `
    -Trigger $trigger `
    -Settings $settings `
    -Principal $taskPrincipal `
    -Description 'Menjalankan Laravel queue worker HRD System saat Windows startup.' `
    -Force | Out-Null

if ($Start) {
    $task = Get-ScheduledTask -TaskName $taskName

    if ($task.State -ne 'Running') {
        Start-ScheduledTask -TaskName $taskName
    }
}

Write-Host "Scheduled Task '$taskName' berhasil didaftarkan."
