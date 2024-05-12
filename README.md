## Getting Started
1. Execute git clone `repsitory url` into terminal in your IDE
2. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
3. Navigate to project and in root directory and run `docker compose build --no-cache` to build fresh images
4. Run `docker compose up --pull always -d --wait` to set up and start a fresh Symfony project
5. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
6. Run `docker compose down --remove-orphans` to stop the Docker containers

## Commands to execute in order to test the PHP Case Test
1. execute `docker ps` in terminal to get application container name, and then execute `docker exec -it container_name bash` to enter into the docker container
2. Inside docker container execute `php bin/console app:insert-weather-condition`  - this command takes json data that I have inserted manually into weatherContition.json and inserts it into weather_condition table. This is done because when we fetch data point "weather" from the API, its value is a code number that corresponds to different value, and when displaying it on front end, Im taking the value from this table for a given code
3. Inside docker container execute `php bin/console app:fetch-weather-data` - this command fetches the data from the given API 
and inserts it into weather_data table. You can also send arguments here such as specific stationId, limit and offset. Default stationId is 06186
4. Refresh `https://localhost` and the table should be populated with the latest 10 inserts, and there should be information about when was the last time the command has been run

## Entering pgAdmin
   NOTICE: I would like to have this done automatically, however due to time constraints I opted to include the setup in the readme file

1. Go to `http://localhost:5050/` and you should be greeted with log in screen on pgAdmin

   NOTICE: These are just localhost credentials and this is not the correct way to expose them. They are still available through the compose.yaml file, however it is much easier here so you can test it
2. Log in with these credentials:
    * `email`: admin@example.com
    * `password`: admin
3. Right click on `Servers` in the left corner and go to `Register -> Server` or `Crate Server` through quicklink

   NOTICE: These are just localhost credentials and this is not the correct way to expose them. They are available through the compose.yaml file, however it is much easier here so you can test it
4. Name your server and go to `Connection` tab and enter the following
    * `Host name`: database
    * `Port`: 5423
    * `Username`: app
    * `Password`: !ChangeMe!
   
   After clicking save, you should have an established connection to our application DB and have access to its tables thorugh the admin panel

