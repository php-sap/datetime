#!/bin/sh
set -eu

root=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
directory=$(mktemp -d)
trap 'rm -rf "$directory"' EXIT

cp "$root/Makefile" "$root/composer.json" "$directory/"
printf 'user-owned lockfile\n' > "$directory/composer.lock"
mkdir "$directory/vendor"

make -s -C "$directory" clean
test "$(cat "$directory/composer.lock")" = 'user-owned lockfile'
test ! -e "$directory/vendor"
