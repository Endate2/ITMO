
INSERT INTO antenna  VALUES
(0, 'Alpha Antenna', 'LTE', 'North Sector', '2023-01-10', '700-800 MHz'),
(1, 'Beta Antenna', '5G', 'East Tower', '2023-02-15', '24-28 GHz'),
(2, 'Gamma Antenna', 'Satellite', 'West Base', '2023-03-20', '10-12 GHz');


INSERT INTO celestial_body  VALUES
(0, 'Earth', 'Planet', 100, 0, 365.25),
(1, 'Jupiter', 'Planet', 80, 588.5, 4332.59),
(2, 'Sun', 'Star', 1000, 149.6, 0);


INSERT INTO observation  VALUES
(0, 0, 1, '2023-04-01', true, 120),
(1, 1, 2, '2023-04-05', false, 90),
(2, 2, 0, '2023-04-10', true, 45);

SELECT tgname, tgrelid::regclass // преобразуем индифекатор таблицы в читаемый вид
FROM pg_trigger // системная таблица котораы содержит все триггеры в базе данных
WHERE tgrelid = 'observation'::regclass;

INSERT INTO event  VALUES
(0, 'Solar Eclipse', 2, '2023-06-10', 'Total solar eclipse over the Pacific.'),
(1, 'Mars Close Approach', 0, '2023-07-15', 'Mars will be closest to Earth.');

INSERT INTO technician  VALUES
(0, 'Alice Johnson', 'Electrical Engineer', 10),
(1, 'Bob Smith', 'Astronomer', 8),
(2, 'Carol Davis', 'Signal Specialist', 5);


INSERT INTO maintenance VALUES
(0, 0, 0, '2023-05-01', 'Performed routine checks and updates.'),
(1, 1, 1, '2023-05-15', 'Calibrated signal reception.');


INSERT INTO weather_condition  VALUES
(0, 0, 'Clear', 15.0, 5.0),
(1, 1, 'Cloudy', 18.0, 10.0);


INSERT INTO signal VALUES
(0, 0, 85, 'UHF', '2023-05-20'),
(1, 1, 78, 'VHF', '2023-05-21'),
(2, 2, 90, 'Microwave', '2023-05-22');
