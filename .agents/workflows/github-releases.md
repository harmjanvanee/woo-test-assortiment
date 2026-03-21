---
description: How to push updates to GitHub and trigger automatic updates in WordPress
---

To ensure your users receive update notifications in their WordPress dashboard, follow these steps for every new version:

### 1. Update Version in Plugin Files
Before pushing to GitHub, update the version number in two places:
- `woo-test-assortiment.php` (Plugin Header: `Version: x.x.x`)
- `woo-test-assortiment.php` (Constant: `define('WTA_VERSION', 'x.x.x');`)

### 2. Commit and Push to GitHub
```bash
git add .
git commit -m "Description of your changes (e.g., Added new feature)"
git push origin main
```

### 3. Create a Git Tag
Create a tag that matches your version number:
```bash
git tag 1.8.2
git push origin 1.8.2
```

### 4. Create a GitHub Release
1. Go to your repository on GitHub.
2. Click on **Releases** (on the right sidebar).
3. Click **Draft a new release**.
4. Select the tag you just pushed (e.g., `1.8.2`).
5. Give the release a title (e.g., `Version 1.8.2`).
6. Click **Publish release**.

### // turbo
### 5. Verify the Update Check
WordPress checks for updates periodically. To force a check:
1. Go to **Dashboard > Updates**.
2. Click **Check Again**.
3. Your plugin should show an update available if the GitHub version is higher than the installed version.

> [!TIP]
> Make sure the ZIP structure is correct if you manually upload a ZIP to the release. The plugin folder should be at the root of the ZIP.
