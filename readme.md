# Trombinoscope

Ce trombinoscope a pour objectif d'être simple à utiliser et à installer sur n'importe quel hébergement PHP avec du SQLite.

Il est né d'un besoin ponctuel et ne prétend pas à l'excellence, mais  peut servir de base facilement modifiable pour des petites structures  (asso, collectif...).

Il inclut un mode d'édition un peu automatisé des champs, et une page de création d'avatar avec import ou copier/collé direct d'image.

# Installation

- Placer le répertoire à l'emplacement souhaité sur votre hébergement.
- copier le fichier ***config-default.php*** sous le nom de ***config.php***.
- Editer ***config.php*** pour changer le mot de passe : la variable **$md5pw** doit contenir le hash md5 de votre mot de passe. Vous pouvez le créer par exemple [ici](https://www.md5.fr/).
- Editer au besoin le fichier ***config.php*** nouvellement créé, notamment pour les champs voulus dans la base de données. La table de travail du trombinoscope sera créée à la première consultation du trombinoscope via un navigateur web.

Veiller à ce que les répertoires ***database*** et ***avatars*** soient bien créés et avec des droits en écriture.

Pour réinitialiser le processus il suffit d'effacer le fichier SQLite, par défaut ***database/contacts.db***

# Démo

Un trombinoscope d'exemple fait avec les splendides illustrations et l'aimable autorisation de [David Revoy](https://www.davidrevoy.com/) est disponible ici : https://jcfrog.com/trombi

Il devrait ressembler à ça :

![index trombinoscope](./doc/trombi-1.png)

# Edition

Pour passer en mode édition il suffit d'ajouter à l'url le paramètre *?editpw=xxxxxx*

Où *xxxxxx* est le mot de passe dont vous avez mis le md5 dans ***config.php***

Le mot de passe par défaut est "trombi".

Pour sortir du mode édition il faut ajouter le paramètre *?editpw=stop*. Un lien permet de le faire d'un clic.

![index edition](./doc/trombi-2.png)

## Ajouter / modifier

L'ajout de fiche se fait en mode édition seulement.

La page de création/édition dispose d'un outil de recherche sur les noms et prénoms. 

![index edition](./doc/trombi-3.png)


### Avatar

Le signe 🖊️ en haut à droite de l'avatar donne accés à une page d'import/collage d'image permettant de rapidement créer un avatar à la bonne dimension et recadré.

![index edition avatar](./doc/trombi-4.png)


# Personalisation

## config.php


## styles.css

