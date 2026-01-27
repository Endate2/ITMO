create table antenna(
   id SERIAL PRIMARY KEY,
   name VARCHAR(50),
   target_location VARCHAR(50),
   installation_date DATE,
   frequency_range VARCHAR(50));

create table celestial_body(
   id SERIAL PRIMARY KEY,
   name VARCHAR(50),
   type VARCHAR(20),
   brightness INTEGER,
   distance_from_earth FLOAT,
   orbital_period FLOAT);

create table observation(
   id SERIAL PRIMARY KEY,
   antenna_id INTEGER REFERENCES antenna(id),
   celestial_body_id INTEGER REFERENCES celestial_body(id),
   observationdate DATE,
   visibility BOOLEAN,
   durationminutes INTEGER);

create table event(
   id SERIAL PRIMARY KEY,
   name VARCHAR(50),
   celestialbodyid INTEGER REFERENCES celestial_body(id),
   event_date DATE,
   place VARCHAR(50));

create table technician(
   id SERIAL PRIMARY KEY,
   name VARCHAR(50),
   specialization VARCHAR(50),
   experience_years INTEGER);

create table maintenance(
   id SERIAL PRIMARY KEY,
   antenna_id INTEGER REFERENCES antenna(id),
   technician_id INTEGER REFERENCES technician(id),
   maintenance_date DATE,
   place VARCHAR(50));

create table weather_condition(
   id SERIAL PRIMARY KEY,
   observation_id INTEGER REFERENCES observation(id),
   weather VARCHAR(50),
   temperature FLOAT,
   wind_speed FLOAT);



CREATE TABLE technician_antenna (
   technician_id INTEGER REFERENCES technician(id),
   antenna_id INTEGER REFERENCES antenna(id),
   PRIMARY KEY (technician_id, antenna_id)
);

/////////
CREATE TABLE StudentCourseInstructor (
        student_id VARCHAR(20),
        course VARCHAR(50),
        instructor VARCHAR(50),
        PRIMARY KEY (student_id, course)
);

CREATE TABLE InstructorCourse (
                                  instructor VARCHAR(50) PRIMARY KEY,
                                  course VARCHAR(50)
);

CREATE TABLE StudentInstructor (
                                   student_id VARCHAR(20),
                                   instructor VARCHAR(50),
                                   PRIMARY KEY (student_id, instructor),
                                   FOREIGN KEY (instructor) REFERENCES InstructorCourse(instructor)
);
/////////