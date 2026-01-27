-- Для таблицы antenna
ALTER TABLE antenna ADD CONSTRAINT chk_antenna_technology 
CHECK (technology IN ('Radio', 'Optical', 'Infrared', 'Microwave', 'X-ray'));

ALTER TABLE antenna ADD CONSTRAINT chk_antenna_installation_date 
CHECK (installation_date <= CURRENT_DATE);

ALTER TABLE antenna  ADD CONSTRAINT chk_name_letters_only 
CHECK (name ~ '^[A-Za-zА-Яа-яЁё]+$');

ALTER TABLE antenna ADD CONSTRAINT chk_frequency_range_numeric 
CHECK (frequency_range ~ '^[A-Za-zА-Яа-яЁё]+$');  



-- Для таблицы celestial_body
ALTER TABLE celestial_body ADD CONSTRAINT chk_celestial_body_name 
CHECK (name ~ '^[A-Za-zА-Яа-яЁё]+$');

ALTER TABLE celestial_body ADD CONSTRAINT chk_celestial_body_type 
CHECK (type IN ('Star', 'Planet', 'Moon', 'Asteroid', 'Comet', 'Galaxy', 'Nebula'));

ALTER TABLE celestial_body ADD CONSTRAINT chk_celestial_body_distance 
CHECK (distance_from_earth >= 0);

ALTER TABLE celestial_body ADD CONSTRAINT chk_celestial_body_orbital_period 
CHECK (orbital_period >= 0);



-- Для таблицы observation
ALTER TABLE observation ADD CONSTRAINT chk_observation_date 
CHECK (observationdate <= CURRENT_DATE);



-- Для таблицы event
ALTER TABLE event ADD CONSTRAINT chk_event_name 
CHECK (name ~ '^[A-Za-zА-Яа-яЁё]+$');

ALTER TABLE event ADD CONSTRAINT chk_event_place 
CHECK (place ~ '^[A-Za-zА-Яа-яЁё]+$');

ALTER TABLE event ADD CONSTRAINT chk_event_date 
CHECK (event_date <= CURRENT_DATE);



-- Для таблицы technician
ALTER TABLE technician ADD CONSTRAINT chk_technician_experience 
CHECK (experience_years >= 0 AND experience_years <= 60);

ALTER TABLE technician ADD CONSTRAINT technician_event_name 
CHECK (name ~ '^[A-Za-zА-Яа-яЁё]+$');

ALTER TABLE technician ADD CONSTRAINT chk_technician_specialization
CHECK (specialization ~ '^[A-Za-zА-Яа-яЁё]+$');



-- Для таблицы maintenance
ALTER TABLE maintenance ADD CONSTRAINT chk_maintenance_date 
CHECK (maintenance_date <= CURRENT_DATE);

ALTER TABLE maintenance ADD CONSTRAINT chk_maintenance_name
CHECK (name ~ '^[A-Za-zА-Яа-яЁё]+$');



-- Для таблицы weather_condition
ALTER TABLE weather_condition ADD CONSTRAINT chk_weather_temp 
CHECK (temperature BETWEEN -100 AND 100);  

ALTER TABLE weather_condition ADD CONSTRAINT chk_weather_wind_speed 
CHECK (wind_speed >= 0 AND wind_speed <= 400);

ALTER TABLE weather_condition ADD CONSTRAINT chk_maintenance_weather
CHECK (weather ~ '^[A-Za-zА-Яа-яЁё]+$');