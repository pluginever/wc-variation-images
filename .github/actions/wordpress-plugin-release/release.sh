#!/bin/bash

set -eo

# output command to log
set -x

#########################################
# SETUP DEFAULTS #
#########################################
VERSION="${VERSION:-${GITHUB_REF#refs/tags/}}"; VERSION="${VERSION#v}"
SLUG="${SLUG:-${GITHUB_REPOSITORY#*/}}"
SVN_URL="https://plugins.svn.wordpress.org/${SLUG}/"
SVN_DIR="${HOME}/svn-${SLUG}"

# If the version is not set, check if package.json exists and get the version from there otherwise exit.
if [[ -z "$VERSION" && -f "$GITHUB_WORKSPACE/package.json" ]]; then
  VERSION=$(node -p "require('./package.json').version")
fi

#########################################
# CHECK IF EVERYTHING IS SET CORRECTLY #
#########################################
for var in USERNAME PASSWORD SLUG VERSION; do
  if [ -z "${!var}" ]; then
    echo "x︎ $var is not set. Exiting..."
    exit 1
  fi
done

# Log the slug.
echo "ℹ︎ SLUG is $SLUG"

# Log the version.
echo "ℹ︎ VERSION is $VERSION"

# Log the SVN URL.
echo "ℹ︎ SVN_URL is $SVN_URL"

#########################################
# PREPARE FILES FOR DEPLOYMENT #
#########################################

# Checkout the SVN repo
echo "➤ Checking out SVN repo..."
svn checkout --depth immediates "$SVN_URL" "$SVN_DIR" >> /dev/null || exit 1
cd "$SVN_DIR" || exit
svn update --set-depth infinity assets >> /dev/null
svn update --set-depth infinity trunk >> /dev/null
svn update --set-depth immediates tags >> /dev/null

# Copy files to the SVN repo
echo "➤ Copying files..."
# If .dist ignore file exists, use it to exclude files from the SVN repo, otherwise use the default.
if [[ -r "$GITHUB_WORKSPACE/.distignore" ]]; then
  echo "ℹ︎ Using .distignore"
  rsync -rc --exclude-from="$GITHUB_WORKSPACE/.distignore" "$GITHUB_WORKSPACE/" trunk/ --delete --delete-excluded
else
  rsync -rc --exclude '.git' --exclude '.github' --exclude '.gitignore' --exclude '.distignore' --exclude 'node_modules' "$GITHUB_WORKSPACE/" trunk/ --delete --delete-excluded
fi

# Remove empty directories from trunk
find trunk -type d -empty -delete

echo "✓ Files copied!"


# Copy assets
# If .wordpress-org is a directory and contains files, copy them to the SVN repo.
if [[ -d "$GITHUB_WORKSPACE/.wordpress-org" ]]; then
  echo "➤ Copying assets..."
  rsync -rc "$GITHUB_WORKSPACE/.wordpress-org/"  assets/ --delete
  # Fix screenshots getting force downloaded when clicking them
  # https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/
  if test -d "$SVN_DIR/assets" && test -n "$(find "$SVN_DIR/assets" -maxdepth 1 -name "*.png" -print -quit)"; then
      svn propset svn:mime-type "image/png" "$SVN_DIR/assets/"*.png || true
  fi
  if test -d "$SVN_DIR/assets" && test -n "$(find "$SVN_DIR/assets" -maxdepth 1 -name "*.jpg" -print -quit)"; then
      svn propset svn:mime-type "image/jpeg" "$SVN_DIR/assets/"*.jpg || true
  fi
  if test -d "$SVN_DIR/assets" && test -n "$(find "$SVN_DIR/assets" -maxdepth 1 -name "*.gif" -print -quit)"; then
      svn propset svn:mime-type "image/gif" "$SVN_DIR/assets/"*.gif || true
  fi
  if test -d "$SVN_DIR/assets" && test -n "$(find "$SVN_DIR/assets" -maxdepth 1 -name "*.svg" -print -quit)"; then
      svn propset svn:mime-type "image/svg+xml" "$SVN_DIR/assets/"*.svg || true
  fi
  echo "✓ Assets copied!"
fi

# Copy tag
echo "➤ Copying tag..."
if svn ls "https://plugins.svn.wordpress.org/$SLUG/tags/$VERSION" >>/dev/null 2>&1; then
	echo "ℹ︎ Tag already exists. Pulling files ..."
	svn update --set-depth infinity "$SVN_DIR/tags/$VERSION"
	rsync -rc "$SVN_DIR/trunk/" "$SVN_DIR/tags/$VERSION/" --delete --delete-excluded
	echo "✓ Tag files synced !"
else
	echo "ℹ︎ Tag does not exist. Creating tag ..."
	svn copy "$SVN_DIR/trunk" "$SVN_DIR/tags/$VERSION" >>/dev/null
	echo "✓ Tag created!"
fi
echo "✓ Tag copied!"


# Update contents.
echo "➤ Preparing files..."
svn add . --force > /dev/null

# SVN delete all deleted files
# Also suppress stdout here
svn status | grep '^\!' | sed 's/! *//' | xargs -I% svn rm %@ > /dev/null
svn update # Fix directory is out of date error
svn status
echo "✓ Files updated!"

# Generate zip file
if $GENERATE_ZIP; then
    echo "➤ Generating zip file..."
    ln -s "${SVN_DIR}/trunk" "${SVN_DIR}/${SLUG}"
    zip -r "${GITHUB_WORKSPACE}/${SLUG}.zip" "$SLUG"
    unlink "${SVN_DIR}/${SLUG}"

    echo "zip_path=${GITHUB_WORKSPACE}/${SLUG}.zip" >> "${GITHUB_OUTPUT}"
    echo "✓ Zip file generated!"
fi

# If dry run, then exit.
if $DRY_RUN; then
  echo "ℹ︎ Dry run: Files not committed."
  exit 0
fi

# Check if there are changes to commit.
if [[ -n "$(svn status "$SVN_DIR")" ]]; then
  echo "➤ Committing changes..."
  echo "✓ Plugin deployed!"
else
  echo "ℹ︎ No changes to commit."
fi
