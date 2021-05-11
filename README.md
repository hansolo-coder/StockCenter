"# StockCenter" 

This is a stock (equities, stocks, bonds etc.) monitoring application written in PHP
Enter your purchases, sales, dividends and other changes in your portfolio.
The application will fetch the current price etc. of each equity from Yahoo showing you the current status of your investments.

This is a fork from https://sourceforge.net/projects/stockcenter/
That version was developed by David Hieber 

The original version could no longer fetch current prices etc from Yahoo due to Yahoo discontinuing that service.
This is a updated version, where it again (at least for now :-) ) is possible to fetch prices from Yahoo, as
well as multiple other restructurings, expansions, improvements and other changes.

IMPORTANT!
1) REMEMBER to take regular backups of the database - it may save you tons of work one day to recreate the data.

2) As this is a work-in-progress, a lot of database-updates are made regularly, but sadly there is no automatic databasemigration
built into this tool yet, as that is a complex process.
Until further, when you clone and install the newest version of the repository, it will most likely balk about database issues
because of schema modifications.
To resolve these:
- Backup the database, copy the backup and stash one of the copies in a safe location.
- I repeat - make sure that you have 2 copies of the database, and that you only modify one of them below.
- For the other copy of the backup, open it using the "sqlite3.exe" (windows) / "sqlite3" (Unix) tool
- Check the current database-schema in the file "classes/db.class.php" and modify the database schema to match the current version.
  This sadly requires database-skills to deduce the required changes. Now is as good a time as any to learn them.
- Upload the database back to the website. It does not matter what you called your local backup of the database - it will
  automatically be renamed
- Use the website to check if the database is ok. Otherwise make more modifications and upload it again. Repeat until it works.
- If you make a horrendous mistake, make a new copy of the database-backup you "stashed" in step 1 and try again. Make sure, that
  you always have an un-modified copy of the backup.


Some screenshots etc can be seen on 

	https://www.onworks.net/software/windows/app-stock-center

