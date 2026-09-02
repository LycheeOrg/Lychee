# Keyboard Shortcuts Cheat Sheet

Reference of every keyboard shortcut wired in the Lychee frontend (v8 Nuxt UI views, identical in the legacy v7 views unless noted).

`Mod` means `Cmd` on Apple devices and `Ctrl` everywhere else.

All shortcuts are ignored while the focus is inside an `input`, `textarea` or `select`, except `Esc`.

## Global

| Key | Action |
| --- | --- |
| `?` | Open the keyboard shortcuts dialog |
| `Mod` + `K` | Open the spotlight search |
| `Esc` | While a modal is open the modal closes and the view below ignores every key. Otherwise: clear the selection if there is one, then blur the focused field, then go back |
| `Enter` | Confirm the current dialog or input |
| `F` | Toggle full screen |
| `H` | Toggle sensitive albums (cycles the NSFW blur overlay when a photo is open) |
| `L` | Open the login dialog |
| `K` | Open passkey login (root albums view only) |
| `Mod` + `V` | Paste an image or video from the clipboard into the upload dialog |

## Albums view (root)

| Key | Action |
| --- | --- |
| `N` | New album |
| `U` | Upload photos |
| `/` | Open search |
| `Mod` + `A` | Select everything |
| `Space` | Clear the selection |
| `M` | Move the selection |
| `Delete` / `Backspace` | Delete the selection |

## Album view (no photo open)

| Key | Action |
| --- | --- |
| `N` | New album |
| `U` | Upload photos |
| `/` | Open search |
| `I` | Toggle the album edit panel |
| `Mod` + `A` | Select every photo and sub-album |
| `Space` | Clear the selection |
| `M` | Move the selection |
| `Delete` / `Backspace` | Delete the selection |
| `Esc` | Clear the selection, then close the open panel, then go back |

## Photo open (album, tag, search, timeline, flow, person)

| Key | Action |
| --- | --- |
| `Left arrow` | Previous photo (flipped in RTL languages) |
| `Right arrow` | Next photo (flipped in RTL languages) |
| `Space` | Start or stop the slideshow |
| `I` | Show or hide the details panel |
| `O` | Cycle the overlay mode (EXIF, description, none) |
| `P` | Toggle the face overlay |
| `E` | Edit the photo information |
| `S` | Highlight the photo (star) |
| `M` | Move the photo |
| `Delete` / `Backspace` | Delete the photo |
| `0` to `5` | Set the rating from 0 to 5 stars (album view) |
| `Esc` | Stop the slideshow, then close the details, then close the photo |

## Face recognition

| Key | Action | Where |
| --- | --- | --- |
| `D` | Dismiss the proposed cluster | Cluster review queue |
| `Right arrow` / `Space` | Skip the proposed cluster | Cluster review queue |
| `Enter` | Assign the cluster to the selected person | Cluster review queue |
| `Mod` + `A` | Select or unselect every face | Face maintenance |
| `Ctrl` + click on a face box | Dismiss that face (desktop only) | Photo view, details drawer |

## Other views

| Key | Action | Where |
| --- | --- | --- |
| `H` | Toggle sensitive content | Statistics |
| `Esc` | Clear the selection if there is one, then go back | Map, Frame, Favourites |
| `Enter` / `Escape` | Save or cancel the inline rename | Bulk album edit, album tracks, person detail |

## Mouse modifiers

| Gesture | Action |
| --- | --- |
| `Mod` + click | Add or remove one item from the selection |
| `Shift` + click | Select the range from the last clicked item |
| Click and drag | Lasso selection (holding `Mod` or `Shift` keeps the current selection) |
| `Left arrow` / `Right arrow` on the rating widget | Move between stars, `Enter` or `Space` applies the rating |

## Embedded widget lightbox

| Key | Action |
| --- | --- |
| `Left arrow` | Previous photo |
| `Right arrow` | Next photo |
| `Space` | Cycle the information mode |
| `Esc` | Close the lightbox |

---

# Aide-mémoire des raccourcis clavier

Référence de tous les raccourcis clavier du frontend Lychee (vues v8 Nuxt UI, identiques dans les vues v7 historiques sauf mention contraire).

`Mod` correspond à `Cmd` sur les appareils Apple et à `Ctrl` partout ailleurs.

Tous les raccourcis sont ignorés quand le focus est dans un `input`, un `textarea` ou un `select`, sauf `Esc`.

## Global

| Touche | Action |
| --- | --- |
| `?` | Ouvrir la fenêtre des raccourcis clavier |
| `Mod` + `K` | Ouvrir la recherche spotlight |
| `Esc` | Quand une fenêtre est ouverte, elle se ferme et la vue en dessous ignore toutes les touches. Sinon : vider la sélection s'il y en a une, puis retirer le focus du champ, puis revenir en arrière |
| `Enter` | Valider la fenêtre ou le champ courant |
| `F` | Basculer en plein écran |
| `H` | Afficher ou masquer les albums sensibles (fait défiler le flou NSFW quand une photo est ouverte) |
| `L` | Ouvrir la fenêtre de connexion |
| `K` | Ouvrir la connexion par passkey (vue racine des albums uniquement) |
| `Mod` + `V` | Coller une image ou une vidéo du presse-papier dans la fenêtre de téléversement |

## Vue albums (racine)

| Touche | Action |
| --- | --- |
| `N` | Nouvel album |
| `U` | Téléverser des photos |
| `/` | Ouvrir la recherche |
| `Mod` + `A` | Tout sélectionner |
| `Space` | Vider la sélection |
| `M` | Déplacer la sélection |
| `Delete` / `Backspace` | Supprimer la sélection |

## Vue album (aucune photo ouverte)

| Touche | Action |
| --- | --- |
| `N` | Nouvel album |
| `U` | Téléverser des photos |
| `/` | Ouvrir la recherche |
| `I` | Afficher ou masquer le panneau d'édition de l'album |
| `Mod` + `A` | Sélectionner toutes les photos et sous-albums |
| `Space` | Vider la sélection |
| `M` | Déplacer la sélection |
| `Delete` / `Backspace` | Supprimer la sélection |
| `Esc` | Vider la sélection, puis fermer le panneau ouvert, puis revenir en arrière |

## Photo ouverte (album, tag, recherche, chronologie, flow, personne)

| Touche | Action |
| --- | --- |
| `Flèche gauche` | Photo précédente (inversée dans les langues RTL) |
| `Flèche droite` | Photo suivante (inversée dans les langues RTL) |
| `Space` | Démarrer ou arrêter le diaporama |
| `I` | Afficher ou masquer le panneau d'informations |
| `O` | Changer le mode d'affichage de la surimpression (EXIF, description, aucune) |
| `P` | Afficher ou masquer la surimpression des visages |
| `E` | Modifier les informations de la photo |
| `S` | Mettre la photo en avant (étoile) |
| `M` | Déplacer la photo |
| `Delete` / `Backspace` | Supprimer la photo |
| `0` à `5` | Attribuer une note de 0 à 5 étoiles (vue album) |
| `Esc` | Arrêter le diaporama, puis fermer les détails, puis fermer la photo |

## Reconnaissance faciale

| Touche | Action | Où |
| --- | --- | --- |
| `D` | Rejeter le groupe proposé | File de révision des groupes |
| `Flèche droite` / `Space` | Passer le groupe proposé | File de révision des groupes |
| `Enter` | Affecter le groupe à la personne sélectionnée | File de révision des groupes |
| `Mod` + `A` | Sélectionner ou désélectionner tous les visages | Maintenance des visages |
| `Ctrl` + clic sur un cadre de visage | Rejeter ce visage (bureau uniquement) | Vue photo, tiroir de détails |

## Autres vues

| Touche | Action | Où |
| --- | --- | --- |
| `H` | Afficher ou masquer le contenu sensible | Statistiques |
| `Esc` | Vider la sélection s'il y en a une, puis revenir en arrière | Carte, Frame, Favoris |
| `Enter` / `Escape` | Valider ou annuler le renommage en ligne | Édition groupée d'albums, pistes audio, fiche personne |

## Modificateurs souris

| Geste | Action |
| --- | --- |
| `Mod` + clic | Ajouter ou retirer un élément de la sélection |
| `Shift` + clic | Sélectionner la plage depuis le dernier élément cliqué |
| Clic maintenu et glissé | Sélection au lasso (maintenir `Mod` ou `Shift` conserve la sélection en cours) |
| `Flèche gauche` / `Flèche droite` sur le widget de note | Se déplacer entre les étoiles, `Enter` ou `Space` applique la note |

## Lightbox du widget embarqué

| Touche | Action |
| --- | --- |
| `Flèche gauche` | Photo précédente |
| `Flèche droite` | Photo suivante |
| `Space` | Changer le mode d'information |
| `Esc` | Fermer la lightbox |
