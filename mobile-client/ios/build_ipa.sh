#!/bin/bash
# ==============================================================================
# NESISTEMA 2.0 - Script Automatizado para Compilar .IPA para TestFlight
# ==============================================================================

set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$DIR"

echo "📦 1. Instalando Pods en iOS..."
cd App
pod install
cd "$DIR"

echo "🧹 2. Creando Archive..."
mkdir -p build
xcodebuild clean archive \
    -workspace App/App.xcworkspace \
    -scheme App \
    -configuration Release \
    -archivePath build/App.xcarchive \
    -destination 'generic/platform=iOS' \
    -allowProvisioningUpdates

echo "📲 3. Exportando archivo .IPA..."
mkdir -p ~/Desktop/NESISTEMA_IPA
xcodebuild -exportArchive \
    -archivePath build/App.xcarchive \
    -exportPath ~/Desktop/NESISTEMA_IPA \
    -exportOptionsPlist ExportOptions.plist \
    -allowProvisioningUpdates

IPA_FILE=$(find ~/Desktop/NESISTEMA_IPA -name "*.ipa" | head -n 1)

if [ -n "$IPA_FILE" ]; then
    cp "$IPA_FILE" ~/Desktop/NESISTEMA.ipa
    echo ""
    echo "🎉 Archivo listo: ~/Desktop/NESISTEMA.ipa"
    open -a Transporter || true
    open ~/Desktop
fi
