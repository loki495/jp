# JP Language Learning App

A Laravel-based application for tracking and reinforcing Japanese language learning, including kana and kanji, using a flashcard-style system.

## Status
Work in progress — core learning features are implemented, but UI polish and additional learning mechanics are still in progress.

## Overview
This app helps track learned vocabulary and characters while reinforcing memory through repetition and simple interactive exercises.

## Tech Stack
- Laravel
- Livewire
- Tailwind CSS

## Features
- Tracking of learned words (hiragana, katakana, kanji)
- Flashcard-style review system
- Simple interactive learning flow
- Structured data model for vocabulary progression

## Importing words from Duolingo
`storage/app/extract.js` pulls `[kana, meaning]` pairs from the Duolingo words page. Import the
resulting JSON with:

```
docker exec jp-app php artisan words:import storage/app/words_YYYY-MM-DD.json \
    --romaji=storage/app/words_YYYY-MM-DD.romaji.json --dry-run
```

Words already in the table are marked learned and take Duolingo's meaning; missing words are added
as learned, with romaji generated from the kana. New words containing kanji (or other non-kana text)
need their romaji in the `--romaji` file (`{"水": "mizu"}`); the command lists any that are missing
and writes nothing. Drop `--dry-run` to apply. Re-running is harmless.

## Practice lists
Besides the built-in learned and hiragana sets, each `storage/app/private/practice_sets/<name>.json`
(a sorted list of word IDs) is a practice list, e.g. `food-and-drink`, `numbers`,
`time-and-calendar`. These files are not tracked in git.

## Planned Improvements
- Spaced repetition system (SRS)
- UI/UX improvements
- Progress tracking and statistics
- Expanded quiz/game modes

## Why I Built This
Duolingo added an "energy" system i would run out of pretty fast, so I made this to keep practicing Japanese at any time.

## Notes
This is an actively developed project. Some features and flows are incomplete or may change.

## Remote access
Also reachable at `https://jp.ac495.net` via a Cloudflare Tunnel, behind Cloudflare
Access. Remote traffic gets built Vite assets (`public/build`) instead of the LAN dev
server, so run `npm run build` after any frontend change for it to show up remotely.
