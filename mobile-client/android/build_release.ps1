$env:JAVA_HOME = 'C:\Users\patri\.jdks\jbr-17.0.14'
$env:Path = "$env:JAVA_HOME\bin;" + $env:Path
Set-Location 'c:\xampp\htdocs\NESISTEMA\NESISTEMA 2.0\mobile-client\android'
.\gradlew.bat bundleRelease assembleRelease
