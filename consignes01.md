en utilisant les bases du framework ZestPHP et en restant à côté du framework ZestPHP

je veux faire un framework ZestPHP web basé sur twig jquery et tailwindcss

au lieu d'inclure le framework ZestPHP core ça inclut le framework ZestPHP web qui lui même inclut le framework ZestPHP core


il va fonctionner de la même manière que le framework ZestPHP core dans app/ et app/webroot

la structure du framework ZestPHP web est la suivante :

tous les fichier statique (css, js, img, etc) seront dans le dossier webroot/static

  zest-fw-web
  app
  app/webroot
  app/webroot/static
  app/webroot/static/css
  app/webroot/static/js
  app/webroot/static/img
  app/webroot/static/fonts
  
prépare l'articualtion en fais des essais dans le dossier zest-fw-web

et apps/fw-v5/app
      
ne touche pas zest-fw-core sauf si tu me demande et si je te dis ok

utilsie le syteme de route de ZestPHP core

utilise le moteur d'api de ZestPHP core

utilise le moteur de template de ZestPHP web que tu va créer


composer se fera dans app/lib

mais il faudra garder les .json dans zest-fw-web/boilerplate/lib

ne pas nommer file.html.twig mais file.twig

il va avoir un système de composant qui seront dans app/static/components

ou {component_name} fera 
app/static/components/{component_name}/{component_name}.php pour la classe
app/static/components/{component_name}/{component_name}.twig pour le template
app/static/components/{component_name}/{component_name}.js pour le js
app/static/components/{component_name}/{component_name}.css pour le css

l'ajax de {component_name} sera dans {component_name}.js et le call api se fera avec {component_name}.php
donc en suivant l'autoload de ZestPHP core
i lfaudra faire un autolaod suplementaire dans ZestPHP WEB qui sera capable  d'appeler la classe {component_name}/{component_name}.php

dans app/static/components

app/ reste agnostic vis a vis du chemin 

donc dans le twig du component ça va charger le css et le jss en fonction de {component_name}