#!/bin/bash
# ==============================================================================
# NESISTEMA 2.0 - Script Automatizado para Compilar .IPA para TestFlight
# ==============================================================================

set -e

echo ""
echo "========================================================="
echo " 🚀 Compilando NESISTEMA 2.0 iOS para TestFlight / Transporter"
echo "========================================================="
echo ""

# Ir al directorio del script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$DIR"

echo "📦 1. Verificando dependencias CocoaPods..."
if ! command -v pod &> /dev/null; then
    echo "⚠️  CocoaPods no está instalado. Instalando con Homebrew o Gem..."
    sudo gem install cocoapods || brew install cocoapods
fi

echo "🔄 2. Instalando Pods en iOS..."
cd ios/App
pod install
cd "$DIR"

echo "🧹 3. Limpiando y creando Archive de Xcode..."
mkdir -p ios/build
xcodebuild clean archive \
    -workspace ios/App/App.xcworkspace \
    -scheme App \
    -configuration Release \
    -archivePath ios/build/App.xcarchive \
    -destination 'generic/platform=iOS' \
    -allowProvisioningUpdates

echo "📲 4. Exportando archivo .IPA firmado para App Store / TestFlight..."
mkdir -p ~/Desktop/NESISTEMA_IPA
xcodebuild -exportArchive \
    -archivePath ios/build/App.xcarchive \
    -exportPath ~/Desktop/NESISTEMA_IPA \
    -exportOptionsPlist ios/ExportOptions.plist \
    -allowProvisioningUpdates

# Buscar el archivo .ipa generado
IPA_FILE=$(find ~/Desktop/NESISTEMA_IPA -name "*.ipa" | head -n 1)

if [ -n "$IPA_FILE" ]; then
    cp "$IPA_FILE" ~/Desktop/NESISTEMA.ipa
    echo ""
    echo "========================================================="
    echo " 🎉 ¡ÉXITO! Tu archivo .IPA está listo en el Escritorio:"
    echo " 📂 ~/Desktop/NESISTEMA.ipa"
    echo "========================================================="
    echo ""
    echo "Abriendo Transporter..."
    open -a Transporter || echo "Abre la app Transporter en tu Mac y arrastra NESISTEMA.ipa"
    open ~/Desktop
else
    echo "⚠️ Se exportó el archivo en ~/Desktop/NESISTEMA_IPA. Revisa esa carpeta."
    open ~/Desktop/NESISTEMA_IPA
fi
