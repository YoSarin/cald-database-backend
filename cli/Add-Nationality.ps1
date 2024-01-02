param(
    [Parameter(Mandatory = $True)]
    [string]$Username,
    [Parameter(Mandatory = $True)]
    [SecureString]$Password
)

$domain = "https://api.evidence.czechultimate.cz"

$token = ((
    Invoke-WebRequest -Uri "$domain/user/login" `
        -Method POST `
        -Body @{
            login = $username;
            password = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($password))
        }
).Content | ConvertFrom-Json).token.token

$newNationality = ((Invoke-WebRequest -Uri "$domain/admin/nationality" -Method POST -Body @{ token = $token; "name" = "Finská"; "country_name" = "Finsko" }).Content | ConvertFrom-Json).data
$newNationality