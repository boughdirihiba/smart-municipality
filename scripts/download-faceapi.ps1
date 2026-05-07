$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$vendorDir = Join-Path $projectRoot 'assets\vendor\face-api'
$modelsDir = Join-Path $vendorDir 'models'

New-Item -ItemType Directory -Force -Path $vendorDir | Out-Null
New-Item -ItemType Directory -Force -Path $modelsDir | Out-Null

Write-Host "Downloading face-api.min.js…"
$faceApiUrl = 'https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js'
Invoke-WebRequest -Uri $faceApiUrl -OutFile (Join-Path $vendorDir 'face-api.min.js')

$weightsBase = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights'
$manifests = @(
  'tiny_face_detector_model-weights_manifest.json',
  'face_landmark_68_model-weights_manifest.json',
  'face_recognition_model-weights_manifest.json'
)

foreach ($manifestName in $manifests) {
  $manifestUrl = "$weightsBase/$manifestName"
  $localManifest = Join-Path $modelsDir $manifestName

  Write-Host "Downloading $manifestName…"
  Invoke-WebRequest -Uri $manifestUrl -OutFile $localManifest

  $json = Get-Content -Raw -Path $localManifest | ConvertFrom-Json
  foreach ($entry in $json) {
    foreach ($p in $entry.paths) {
      $fileUrl = "$weightsBase/$p"
      $localFile = Join-Path $modelsDir $p
      if (Test-Path $localFile) {
        continue
      }
      Write-Host "  -> $p"
      Invoke-WebRequest -Uri $fileUrl -OutFile $localFile
    }
  }
}

Write-Host "Done. Files installed to: $vendorDir" -ForegroundColor Green
