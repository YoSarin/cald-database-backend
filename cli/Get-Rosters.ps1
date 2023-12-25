param(
    [string]$TournamentName,
    [Parameter(Mandatory = $True)]
    [int]$Season,
    [Parameter(Mandatory = $True)]
    [string]$Username,
    [Parameter(Mandatory = $True)]
    [SecureString]$Password
)

function normalize($string)
{
    [text.encoding]::ascii.getstring([text.encoding]::getencoding(1251).getbytes($string))
}

$domain = "https://api.evidence.czechultimate.cz"

$token = ((
    Invoke-WebRequest -Uri "$domain/user/login" `
        -Method POST `
        -Body @{
            login = $username;
            password = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($password))
        }
).Content | ConvertFrom-Json).token.token

$seasonData = ((Invoke-WebRequest -Uri "$domain/list/season?filter[name]=$Season" -Body @{ token = $token }).Content | ConvertFrom-Json).data
$tournamentData = ((Invoke-WebRequest -Uri "$domain/list/tournament?filter[season_id]=$($seasonData.id)" -Body @{ token = $token }).Content | ConvertFrom-Json).data
if ($TournamentName) {
    $tournamentData = $tournamentData | Where-Object { $(normalize($_.Name)) -match $(normalize($TournamentName)) }
}

if ($TournamentData.Count -gt 1) {
    Write-Warning "Prosím vyber turnaj: "
    $tournamentData | ForEach-Object {
        Write-Host "[$($_.Id)] $($_.Name)"
    }
    $id = Read-Host "ID"

    $tournamentData = $tournamentData | Where-Object { $_.Id -eq $id }

} elseif ($TournamentData.Count -eq 0) {
    Write-Error "Špatné jméno turnaje, v sezóně '$Season' není žádný turnaj jehož jméno by obsahovalo '$(normalize($TournamentName))'"
    exit
}

$leagueDivisionData = ((Invoke-WebRequest -Uri "$domain/list/tournament_belongs_to_league_and_division?filter[tournament_id]=$($tournamentData.id)" -Body @{ token = $token }).Content | ConvertFrom-Json).data
$rostersData = ((Invoke-WebRequest -Uri "$domain/list/roster?filter[tournament_belongs_to_league_and_division_id]=$($leagueDivisionData.id)" -Body @{ token = $token }).Content | ConvertFrom-Json).data

$rostersData | ForEach-Object {
    $teamData = ((Invoke-WebRequest -Uri "$domain/list/team?filter[id]=$($_.team_id)" -Body @{ token = $token }).Content | ConvertFrom-Json).data
    $teamRostersData = ((Invoke-WebRequest -Uri "$domain/list/player_at_roster?filter[roster_id]=$($_.id)&extend=1" -Body @{ token = $token }).Content | ConvertFrom-Json).data
    $actualRosterData = $_

    $teamRostersData | Foreach-Object { @{
        "team" = $teamData.Name
        "roster_name" = $actualRosterData.Name
        "first_name" = $_.player.first_name
        "last_name" = $_.player.last_name
        "jersey_number" = $_.player.jersey_number
    }}
}