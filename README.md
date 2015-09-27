# Team Website

## Development instructions

1.  Have PHP installed
2.  Have SQLite installed
3.  Setup the local db
    1.  Navigate into `/app/database`
    2.  Delete `bolt.db`, if it already exists
    3.  Run `sqlite3 bolt.db < boltdb-backup.bak` to create the database
    4.  To create a new backup, run `sqlite3 bolt.db .dump > boltdb-backup.back`
    5.  Navigate back up to the root directory
2.  Run `php -S localhost:8000 -t . index.php` from the command line
3.  Visit `localhost:8000` to see the website
4.  Visit `localhost:8000/bolt` to access the admin panel
    -   Username == `devadmin`, password == `password`.
    -   Note that the username and password is only for the dev build. The actual server
        should obviously not use these credentials

Note: in development, the website is currently set up to automatically create and use a 
SQLite database. This may change in the future.

## Production instructions

None yet

## Todo

-   Get the website working well on mobile and tablets
    -   Make centerpiece images responsive
    -   Use facebook-style pullout nav on smaller screens instead of the
        usual navbar  
-   Finish writing templates for all custom pages
    -   Team/team rosters
    -   Sponsors
    -   News/blog
-   Transfer all content and images over
-   Figure out specific and concrete instructions for deploying
-   Figure out how to integrate website with other webpages
-   Figure out how to simplify admin panel UI/iron out some of bolt's 
    rough edges/write instructions
-   Verify that website looks ok on older versions of IE/corporate-style
    computers
-   Write instructions for setting up and using Composer
-   Write instructions on deploying to production
