plugins {
    kotlin("jvm") version "2.1.10"
    java
    war
}

group = "org.example"
version = "1.0-SNAPSHOT"

repositories {
    mavenCentral()
}

dependencies {
    compileOnly("javax.servlet:javax.servlet-api:4.0.1")

    implementation("com.fasterxml.jackson.core:jackson-databind:2.15.2")
    implementation("com.fasterxml.jackson.core:jackson-core:2.13.4")
    implementation("com.fasterxml.jackson.core:jackson-annotations:2.13.4")

    implementation("javax.json:javax.json-api:1.1.4")
    implementation("org.glassfish:javax.json:1.1.4")
}


tasks.test {
    useJUnitPlatform()
}
kotlin {
    jvmToolchain(17)
}
java {
    toolchain {
        languageVersion.set(JavaLanguageVersion.of(17)) // WildFly 21 лучше работает с Java 11
    }
}
tasks.war {
    archiveFileName.set("lab2.war")
}