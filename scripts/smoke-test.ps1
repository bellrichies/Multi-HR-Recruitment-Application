param(
    [string] $BaseUrl = "http://127.0.0.1:8080"
)

php "$PSScriptRoot/../backend/bin/smoke_test.php" $BaseUrl
