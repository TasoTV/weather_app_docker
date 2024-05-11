## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to set up and start a fresh Symfony project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Commands to execute in order to test the PHP Case Test
1. execute `docker ps` in terminal to get container name, and then execute `docker exec -it container_name bash` to enter into the docker container
2. Inside docker container execute `php bin/console app:insert-weather-condition`  - this command takes json data that I have inserted manually into weatherContition.json and inserts it into weather_condition table. This is done because when we fetch data point "weather" from the API, its value is a code number that corresponds to different value, and when displaying it on front end, Im taking the value from this table for a given code
3. Inside docker container execute `php bin/console app:fetch-weather-data` - this command fetches the data from the given API 
and inserts it into weather_data table
4. Refresh `https://localhost` and the table should be populated with the latest 10 inserts, and there should be information about when was the last time the command has been run
