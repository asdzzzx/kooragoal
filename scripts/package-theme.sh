#!/usr/bin/env bash
set -euo pipefail

THEME_DIR="$(dirname "$0")/../kooragoal-theme"
DIST_DIR="$(dirname "$0")/../dist"
ZIP_NAME="kooragoal-theme.zip"

if [ ! -d "$THEME_DIR" ]; then
  echo "[error] لم يتم العثور على مجلد القالب في $THEME_DIR" >&2
  exit 1
fi

if [ ! -f "$THEME_DIR/style.css" ]; then
  echo "[error] ملف style.css غير موجود في جذر القالب، ولن يتم إنشاء الحزمة." >&2
  exit 1
fi

mkdir -p "$DIST_DIR"

pushd "$THEME_DIR" >/dev/null
zip -r "$DIST_DIR/$ZIP_NAME" . -x '*.DS_Store'
popd >/dev/null

echo "تم إنشاء $DIST_DIR/$ZIP_NAME ويمكن رفعه مباشرة إلى ووردبريس."
