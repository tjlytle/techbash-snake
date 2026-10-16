# bin/play.ps1 — play the TechBash snake from a Windows laptop, board in the browser.
#
#   .\play.ps1                      our snake alone
#   .\play.ps1 easy                 vs the basic random snake and the two official starters
#   .\play.ps1 mid                  vs snork and hobbs
#   .\play.ps1 hard                 vs shapeshifter, snork and hobbs
#   .\play.ps1 all                  everyone on the board (7 snakes)
#   .\play.ps1 hard -Delay 400      slower turns for the room (ms per turn, default 250)
#   .\play.ps1 -Name you -Url https://their-snake.example   add any snake by name and URL
#
# Needs the rules CLI (battlesnake.exe) on PATH: https://github.com/BattlesnakeOfficial/rules/releases
# Every snake here is public through the lytlelabs.com tunnel, so this runs from anywhere.
param(
    [ValidateSet("solo", "easy", "mid", "hard", "all")] [string] $Pairing = "solo",
    [int] $Delay = 250,
    [int] $Timeout = 1000,
    [string[]] $Name = @(),
    [string[]] $Url = @(),
    [switch] $NoBrowser
)

$us = @{ name = "techbash"; url = "https://snake.lytlelabs.com" }

$snakes = @{
    random       = "https://random.lytlelabs.com"                  # the platform's basic snake
    "starter-py" = "https://starter-py.lytlelabs.com"              # official Python starter
    "starter-js" = "https://starter-js.lytlelabs.com"              # official JavaScript starter
    snork        = "https://snork.lytlelabs.com"                   # wrenger/snork (strong)
    hobbs        = "https://hobbs.lytlelabs.com/hovering-hobbs"    # coreyja (strong)
    devin        = "https://hobbs.lytlelabs.com/devious-devin"     # coreyja (aggressive)
    shapeshifter = "https://shapeshifter.lytlelabs.com"            # JonathanArns (elite 2022)
}

$pairings = @{
    solo = @()
    easy = @("random", "starter-py", "starter-js")
    mid  = @("snork", "hobbs")
    hard = @("shapeshifter", "snork", "hobbs")
    all  = @("random", "starter-py", "starter-js", "snork", "hobbs", "devin", "shapeshifter")
}

$args_ = @("play", "-W", "11", "-H", "11", "-t", "$Timeout", "-d", "$Delay", "--name", $us.name, "--url", $us.url)
foreach ($n in $pairings[$Pairing]) { $args_ += @("--name", $n, "--url", $snakes[$n]) }
for ($i = 0; $i -lt $Name.Count; $i++) {
    if ($i -lt $Url.Count) { $args_ += @("--name", $Name[$i], "--url", $Url[$i]) }
}
if (-not $NoBrowser) { $args_ += "--browser" }

Write-Host "battlesnake $($args_ -join ' ')" -ForegroundColor DarkGray
& battlesnake @args_
