# Scramble Egg Converter

Adds an admin-only sidebar page to export eggs as:

- Pelican JSON
- Pterodactyl JSON (converted from Pelican export format)

## Install

1. Ensure this plugin exists at `plugins/scramble-egg-converter`.
2. Open the Panel and install/enable the plugin from the Plugins section.
3. Open **Admin Panel -> Server -> Scramble Converter**.

## Notes

- This plugin is restricted to the `admin` panel in `plugin.json`.
- Access is limited to users that can `export egg`.
