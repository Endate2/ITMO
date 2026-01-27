SELECT 
    "ОТЧЕСТВО", 
    COUNT("ОТЧЕСТВО") 
FROM "Н_ЛЮДИ"
WHERE "ИД" IN (
    SELECT "ЧЛВК_ИД" 
    FROM "Н_ОБУЧЕНИЯ" 
    WHERE "ЧЛВК_ИД" IN (
        SELECT "ЧЛВК_ИД" 
        FROM "Н_УЧЕНИКИ" 
        WHERE "ПЛАН_ИД" IN (
            SELECT "ПЛАН_ИД" 
            FROM "Н_ПЛАНЫ" 
            WHERE "ОТД_ИД" = (
                SELECT "ОТД_ИД" 
                FROM "Н_ОТДЕЛЫ" 
                WHERE "ИМЯ_В_ИМИН_ПАДЕЖЕ" = 'кафедра вычислительной техники'
            )
        )
    )
)
GROUP BY "ОТЧЕСТВО"
HAVING COUNT("ОТЧЕСТВО") != 50
ORDER BY "ОТЧЕСТВО";