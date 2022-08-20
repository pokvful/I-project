# I-project

Auction website, created in the last period of my first school year of the HAN.

## Vereiste programma's

Voor het opzetten van de website zijn de volgende programma’s nodig:
•	Microsoft SQL Server v18.9.1 (microsoft team, 2021);
•	PHP v7.4.16;
•	Apache v.2.4.46;
•	Composer (composer, 2021);
o	Latte v2.10.3 (latte team, 2021);
o	Tracy v2.8.4 (libraries, 2021);
o	Bootstrap v.5.0.0-beta3 (Bootstrap, 2021);
o	PHP-mailer v6 (LeonMelis, 2021);

## Database

De database kan worden aangemaakt met een aantal scripts. Eerst moeten de volgende scripts worden uitgevoerd: CREATE_SCRIPT_EENMAALANDERMAAL.sql, INSERT_SCRIPT_EENMAALANDERMAAL.sql en CREATE_VIEWS_SCRIPT_EENMAALANDERMAAL.sql. Hiermee wordt de database aangemaakt met de correcte tabellen en wordt er al een heleboel mock data ingevoerd (LET OP, het script verwijderd de database, dus er kan data verloren gaan). Hierna moet er eerst een gebruiker aangemaakt worden, zodat de website bij de database kan. Hoe dit werkt kunt u hier lezen. De gebruiker in ieder geval de volgende rechten hebben:

- db_datareader;
- db_datawriter;
- db_owner.

Daarna moeten de volgende dingen worden uitgevoerd in een terminal:

```$ php /path/to/Rubric.php “{host}” “{username}” “{password}” “{name}”```

```$ php /path/to/hash_user_passwords.php “{host}” “{username}” “{password}” “{name}”```

Waar {host} de host van de database is, {username} de gebruiker die is aangemaakt, {password} het wachtwoord voor die gebruiker en {name} de naam van het wachtwoord.
Wanneer dit allemaal is gedaan, moet nog als laatste het Items_in_rubric.sql script worden uitgevoerd. Hierna is de database opgezet.
