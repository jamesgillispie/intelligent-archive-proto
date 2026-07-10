#!/usr/bin/env bash

set -eu
# The script will ask for the project name and then download the project files from the repository. It will rename the project files with the project name and install the dependencies.
# The script will also initialize a git repository and commit the changes.

# CONSTANTS
GREEN="\033[32m"
NC="\033[0m" # No Color


project_name="${1:-null}"

if [  "$project_name" == null ]; then
echo "${GREEN}Start setting up the project${NC}"
read -rp "Project name (Laravel Twill Project): " project_name
project_name=${project_name:-'Laravel Twill Project'}
fi

echo "${GREEN}Booting new project with name $project_name ${NC}"

slug_project_name=$(echo "$project_name" | tr '[:upper:]' '[:lower:]' | tr ' ' '-')

# Rename the project files
echo "Renaming the project files with the project name: ${GREEN}$project_name${NC}"
files=( "composer.json" "package.json" "composer.lock" "package.lock" ".env.example" "README.md" ".gitlab/deploy.example.yaml" )

for file in "${files[@]}"
do
    if test -f "$file"; then
    echo "Renaming $file"
    sed -i "" "s|%slug-project-name%|$slug_project_name|g" $file
    sed -i "" "s|laravel-twill-project/|$slug_project_name/|g" $file
    sed -i "" "s|%project-name%|$project_name|g" $file
    fi
done


# Update dependencies
read -rp "Install dependencies? (default: y)" install_dependencies
install_dependencies=${install_dependencies:-'y'}
if [[ "$install_dependencies" == "y" ]] || [[ "$install_dependencies" == "yes" ]]; then
    rm -rf composer.lock
    rm -rf package-lock.json
    composer install
    npm install
fi


# Remove install files
echo "Removing install files"
rm -rf .gitlab/deploy.yaml
mv .gitlab/deploy.example.yaml .gitlab/deploy.yaml
rm -rf install.sh
rm -rf INSTALL.md

echo "Init git repository"
rm -rf .git
git init .
git add --all
git commit -m "Initial commit"
echo "${GREEN}Git repository initialized and first commit done!${NC}"

echo "${GREEN}Setup done!${NC}"
echo "Time to code! 🚀"
