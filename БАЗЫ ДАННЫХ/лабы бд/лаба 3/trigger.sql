CREATE OR REPLACE FUNCTION check_observation_compatibility_simple()
RETURNS TRIGGER AS $$
DECLARE
antenna_freq_range VARCHAR(50);
    body_type VARCHAR(20);
    compatible BOOLEAN := FALSE;

BEGIN

SELECT frequency_range INTO antenna_freq_range FROM antenna WHERE id = NEW.antenna_id;
SELECT type INTO body_type FROM celestial_body WHERE id = NEW.celestial_body_id;


body_type := LOWER(body_type);

    IF (body_type = 'planet' AND antenna_freq_range IN ('HF', 'VHF', 'UHF', 'L-band', 'S-band')) THEN
        compatible := TRUE;
    ELSIF (body_type = 'star' AND antenna_freq_range IN ('HF', 'VHF', 'UHF', 'L-band', 'S-band', 'Optical')) THEN
        compatible := TRUE;
    ELSIF (body_type IN ('galaxy', 'nebula') AND antenna_freq_range = 'Optical') THEN
        compatible := TRUE;
    ELSIF (body_type IN ('pulsar', 'quasar') AND antenna_freq_range = 'X-band') THEN
        compatible := TRUE;
END IF;

    IF NOT compatible THEN
        RAISE EXCEPTION 'Антенна с частотным диапазоном % не подходит для наблюдения небесного тела типа %',
                         antenna_freq_range, body_type;
END IF;

RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER observation_compatibility_check_simple
    BEFORE INSERT OR UPDATE ON observation
    FOR EACH ROW
    EXECUTE FUNCTION check_observation_compatibility_simple();





INSERT INTO observation (antenna_id, celestial_body_id, observationdate, visibility, durationminutes)
VALUES (1, 1, '2023-01-01', TRUE, 60);


INSERT INTO observation (antenna_id, celestial_body_id, observationdate, visibility, durationminutes)
VALUES (6, 2, '2023-01-02', TRUE, 120);