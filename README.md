# b-stats-thumbs

This repository contains the code that manages the thumbnails on the b-stats archive.

## Requirements
- Apache 2.4 or equivalent
- PHP 7 with cURL and MySQLi
- MariaDB

## Set-up
Copy `backend/cfg.json.sample` to `backend/cfg.json` and customize your settings there:

- In the `mysql` section, fill in the host, username, password, and database you want to use.
- In the `boards` array, place the board shortnames that you want to download thumbs for.
- Replace the domain (and protocol) for the `site` to point to your b-stats installation domain.
- For 4chan, you can leave the `format` as-is. If you want to get your thumbs from somewhere else, the available wildcards are:
  - `%board%` ("b", "f")
  - `%tim%` ("143245444123")
  - `%ext%` (".jpg", ".png")
  - `%filename%` ("image")

## Saving thumbs
Right now the script runs in your terminal: `php dl-thumbs.php`

I recommend using GNU Screen to run the script

## To Do
In the future, this should integrate with the archive itself, probably with an API for communication if it is on a different server.