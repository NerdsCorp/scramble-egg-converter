# Scramble Egg Converter
## Photos
<img width="3412" height="1258" alt="image" src="https://github.com/user-attachments/assets/f66a4239-2321-4605-94f9-4be7113fe089" />

## Description
A [Pelican Panel](https://pelican.dev) plugin for egg developers who want to contribute to both communities by converting and exporting eggs across platforms. Inspired by [@redthirten's web tool](https://redthirten.github.io/scramble-egg-converter/) — check out [his repo here](https://github.com/redthirten/scramble-egg-converter).

Adds an admin-only sidebar page with support for exporting eggs as:

- **Pelican JSON** or **Pelican YAML**
- **Pterodactyl JSON** (converted from Pelican's export format)

## Installation

1. Download the plugin `.zip` file
2. Unzip and remove any nested folders, then rename the folder to `pwa-plugin`
3. Re-zip the folder
4. In your panel, navigate to **Admin → Plugins**
5. Click **Import** and install the plugin

## Notes

- This plugin is restricted to the `admin` panel via `plugin.json`
- Access is limited to users with the `export egg` permission
