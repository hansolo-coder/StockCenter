"# StockCenter" 

This is a stock (equities, stocks, bonds etc.) monitoring application written in PHP.
Enter your purchases, sales, dividends and other changes in your portfolio.
The application will fetch the current price etc. of each equity from Yahoo showing you the current status of your investments.

This is a fork from https://sourceforge.net/projects/stockcenter/
That version was developed by David Hieber.

The original version could no longer fetch current prices etc. from Yahoo due to Yahoo discontinuing the previous service.
This is a updated version, where it again (at least for now :-) ) is possible to fetch prices from Yahoo, as
well as with multiple other restructurings, expansions, improvements and other changes.

The current version is a WORK-IN-PROGRESS (2021-2023). The current version pushed, is supposed to be bug-free (at least of serious bugs)

IMPORTANT!
1) REMEMBER to take regular backups of the database - it may save you tons of work one day to recreate the data.

2) As this is a work-in-progress, several database-schema updates are made regularly, but I have not prioritized yet to add automatic databasemigration
 yet, as that is a complex process. Sorry.
Until further, when you clone and install the newest version of the repository, it will possibly balk about database issues
because of schema modifications.
To resolve these:
- Backup the database, copy the backup and stash one of the copies in a safe location.
- I repeat - make sure that you have 2 copies of the database, and that you only modify one of them below.
- For the other copy of the backup, open it using the "sqlite3.exe" (windows) / "sqlite3" (Unix) tool
	- On Linux it can be found in the repository - On Debian: apt install sqlite3
	- For Windows, it can be found at https://www.sqlite.org/download.html (look for 'sqlite-tools-win32-x86')
- Check the current database-schema in the file "classes/db.class.php" and modify the database schema to match the current version.
  It requires database-skills to deduce the required changes. Now is as good a time as any to learn it.
- Upload the database back to the website. It does not matter what you called your local backup of the database - it will
  automatically be renamed on upload.
- Use the website to check if the database is ok. Otherwise make more modifications and upload it again. Repeat until it works.
- If you make a horrendous mistake, make a new copy of the database-backup you "stashed" in step 1 and try again. Make sure, that
  you always have an un-modified copy of the backup.


Some screenshots etc. from the original version can be seen at:

	https://www.onworks.net/software/windows/app-stock-center

