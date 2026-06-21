1) Sklonować repozytorium z brancha 'master'.
2) Wykonać:
   composer install //(jeśli nie posiadamy composera - doinstalować go)
3) Wykonać:
   docker-compose build //(jeśli nie posiadamy dockera - doinstalować go)
4) Uruchomić kontenery za pmocą:
   docker-compose up -d
5) W pliku app\.env w folderze app link dostępowy do bazy danych powinien miec postać:
   DATABASE_URL=mysql://symfony:symfony@mysql:3306/symfony
6) (W przypadku uruchamiania testów) w pliku app\.env.test w folderze app link dostępowy do bazy danych powinien miec postać:
   DATABASE_URL="sqlite:///%kernel.project_dir%/var/test.db"
7) W celu wypełnienia bazy przykładowymi danymi należy wejść do kontenera PHP komendą:
   docker compose exec php bash
   A następnie wykonać komendę:
   bin/console doctrine:fixtures:load
   
   
