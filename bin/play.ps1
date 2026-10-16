# bin/play.ps1 — play the TechBash snake from a Windows laptop, board in the browser.
#
#   .\play.ps1                      our snake alone
#   .\play.ps1 easy                 vs the two official starter snakes
#   .\play.ps1 mid                  vs snork and hobbs
#   .\play.ps1 hard                 vs shapeshifter, snork and hobbs
#   .\play.ps1 all                  everyone on the board (6 snakes)
#   .\play.ps1 room                 vs up to 3 of the snakes the room submitted, picked at random (-RoomMax 5 for more)
#   .\play.ps1 room-hard            the room plus shapeshifter and snork
#   .\play.ps1 hard -Delay 400      slower turns for the room (ms per turn, default 250)
#   .\play.ps1 -Name you -Url https://their-snake.example   add any snake by name and URL
#
# Needs the rules CLI (battlesnake.exe) on PATH: https://github.com/BattlesnakeOfficial/rules/releases
# Every snake here is public through the lytlelabs.com tunnel, so this runs from anywhere.
#
# The board always opens in ONE dedicated browser window (its own Chrome or Edge profile under
# %LOCALAPPDATA%\snake-board): park that window on its own desktop and every game lands there as a
# new tab. The rules CLI opens URLs through rundll32, so a stand-in rundll32.cmd is put first on
# PATH for the duration of the game. -Browser edge|chrome picks which; -NoWindow uses the default browser.
#
# Easiest: play.cmd next to this file runs it with the execution policy bypassed:  .\play easy -Delay 400
# Or once:  Set-ExecutionPolicy -Scope CurrentUser RemoteSigned, then .\play.ps1 works directly.
param(
    [ValidateSet("solo", "easy", "mid", "hard", "all", "room", "room-hard")] [string] $Pairing = "solo",
    [int] $Delay = 250,
    [int] $Timeout = 1000,
    [int] $RoomMax = 3,
    [string[]] $Name = @(),
    [string[]] $Url = @(),
    [switch] $NoBrowser,
    [switch] $NoWindow,
    [ValidateSet("chrome", "edge")] [string] $Browser = ""
)

$us = @{ name = "techbash"; url = "https://snake.lytlelabs.com" }

$snakes = @{
    "starter-py" = "https://starter-py.lytlelabs.com"              # official Python starter
    "starter-js" = "https://starter-js.lytlelabs.com"              # official JavaScript starter
    snork        = "https://snork.lytlelabs.com"                   # wrenger/snork (strong)
    hobbs        = "https://hobbs.lytlelabs.com/hovering-hobbs"    # coreyja (strong)
    devin        = "https://hobbs.lytlelabs.com/devious-devin"     # coreyja (aggressive)
    shapeshifter = "https://shapeshifter.lytlelabs.com"            # JonathanArns (elite 2022)
}

# Snakes from the room, added as Tim approves them on the channel (name = URL, one per line).
$room = [ordered]@{
    "grok-bot" = "https://sweet-rice-learn.loca.lt"
}

$pairings = @{
    solo = @()
    easy = @("starter-py", "starter-js")
    mid  = @("snork", "hobbs")
    hard = @("shapeshifter", "snork", "hobbs")
    all  = @("starter-py", "starter-js", "snork", "hobbs", "devin", "shapeshifter")
    room = @()
    "room-hard" = @("shapeshifter", "snork")
}
foreach ($k in $room.Keys) { $snakes[$k] = $room[$k] }
# A room game takes at most 3 of the submitted snakes, picked at random each time (-RoomMax changes it).
if ($Pairing -like "room*") {
    $picked = @($room.Keys)
    if ($picked.Count -gt $RoomMax) { $picked = @($picked | Get-Random -Count $RoomMax) }
    $pairings[$Pairing] = $picked + $pairings[$Pairing]
}

$args_ = @("play", "-W", "11", "-H", "11", "-t", "$Timeout", "-d", "$Delay", "--name", $us.name, "--url", $us.url)
foreach ($n in $pairings[$Pairing]) { $args_ += @("--name", $n, "--url", $snakes[$n]) }
for ($i = 0; $i -lt $Name.Count; $i++) {
    if ($i -lt $Url.Count) { $args_ += @("--name", $Name[$i], "--url", $Url[$i]) }
}
if (-not $NoBrowser) { $args_ += "--browser" }

$oldPath = $env:PATH
if (-not $NoBrowser -and -not $NoWindow) {
    $chrome = @("$env:ProgramFiles\Google\Chrome\Application\chrome.exe", "${env:ProgramFiles(x86)}\Google\Chrome\Application\chrome.exe", "$env:LOCALAPPDATA\Google\Chrome\Application\chrome.exe") | Where-Object { Test-Path $_ } | Select-Object -First 1
    $edge   = @("${env:ProgramFiles(x86)}\Microsoft\Edge\Application\msedge.exe", "$env:ProgramFiles\Microsoft\Edge\Application\msedge.exe") | Where-Object { Test-Path $_ } | Select-Object -First 1
    $exe = if ($Browser -eq "chrome") { $chrome } elseif ($Browser -eq "edge") { $edge } else { if ($chrome) { $chrome } else { $edge } }
    if ($exe) {
        $home_ = Join-Path $env:LOCALAPPDATA "snake-board"
        $bin = Join-Path $home_ "bin"; $profile_ = Join-Path $home_ "profile"
        New-Item -ItemType Directory -Force -Path $bin, $profile_ | Out-Null
        # rundll32 url.dll,FileProtocolHandler <url>  →  open <url> in the dedicated profile window
        "@echo off`r`nstart `"`" `"$exe`" --user-data-dir=`"$profile_`" --no-first-run `"%~2`"`r`n" | Set-Content -Path (Join-Path $bin "rundll32.cmd") -Encoding ASCII
        $env:PATH = "$bin;$env:PATH"
        Write-Host "board window: $(Split-Path $exe -Leaf) profile in $profile_" -ForegroundColor DarkGray
    } else {
        Write-Host "no Chrome or Edge found; using the default browser" -ForegroundColor Yellow
    }
}

Write-Host "battlesnake $($args_ -join ' ')" -ForegroundColor DarkGray
try { & battlesnake @args_ } finally { $env:PATH = $oldPath }
