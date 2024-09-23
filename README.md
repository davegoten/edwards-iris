# Edwards Iris Classification System (standalone version)

### Table of Contents
* [Important Security Notice](#important-security-notice)
* [Installation](#installation)
  * [Required programs](#required-programs)
  * [Downloading this code](#downloading-this-code)
  * [Docker Configuration & Customizations](#docker-configuration-&-customizations)
  * [Starting the server](#starting-the-server)
  * [Stopping the server](#stopping-the-server)
* [What's going on](#what's-going-on)
  * [Virtual Machine Setup](#virtual-machine-setup)
  * [Volumes](#volumes)
* [Why is there a standalone version](#why-is-there-a-standalone-version)
  * [Privacy concerns](#privacy-concerns)
  * [Updates](#updates)
* [Troubleshooting](#troubleshooting)
  * [Quick Tips](#quick-tips)
  * [Upload shortcuts](#upload-shortcuts)
  * [Reset everything](#reset-everything)


### Important Security Notice
This code is out of date and contains code that is quite old. It is likely to 
contain some vulnerabilities. This appication should not be used hosted as is
anywhere, though it should be fine for local use. It is largely for this reason
that I've decided to stop hosting this code on a website and provided it as a 
docker container instead. Specifcally this is reffering to the outdated version
of jquery and three.js though there maybe others.

### Installation
In order to run this program locally your system you can use a virtulized web 
server run on your own computer. The system requirements are determiend by 
Docker Desktop, which is the program that allows you to run the virutal 
machines.

Additionally some disk space will be required to run everything and host your
own images, create some new images, and of course host the source files and 
virtual machine images.

Optionally, if you understand how to set up your own web server and have the 
skillset to run your own web server, you can take these files and add them to 
your web's document root and serve the php files manually. I assume that if 
you're able to do that, then you wont need any further instructions. There are 
no special override rules or rewrites. The only key parts are to make sure that
index.php is your DirectoryIndex and that you do not allow Indexes (or 
autoindex is off).

##### Required programs
In order for this code to run locally you will need to install 
[Docker Desktop](https://docs.docker.com/get-docker/) or some other similar 
docker program that can run docker-compose. This will allow you to run a set of
virtual machines locally.

(optionally, recommended) You can also download and install 
[git](https://git-scm.com/downloads) which is a version control program. It
is only used to download this code in this case.

##### Downloading this code
if you installed git, then you can click the Code button in the top right of
github, copy the clone code and run that in the command line 
(cmd, powershell, gitbash, terminal, etc).

Windows (open command by pressing `Windows Key + R` and typing cmd):
```
cd %USERPROFILE%\Desktop
git clone git@github.com:davegoten/edwards-iris.git iris
```
Windows Powershell OR
Mac (open terminal by pressing `command + spacebar` and typing 
`terminal.app`):
```
cd ~/Desktop
git clone git@github.com:davegoten/edwards-iris.git iris
```

Otherwise you can use the download zip link in the same code button, and extract 
that to a folder on your computer.

##### What to customize
In the next section below we'll talk about how to customize, but right away you 
should immedately customize.

* SALT_PHRASE
* DEFAULT_USER

`SALT_PHRASE` will add a layer of safty to your database passwords. If everyone
uses the same default one, then that's less safe then if you use your own.

`DEFAULT_USER` will be your super admin user login name. Same as earlier, if 
everyone uses the same login name then it'll be less safe then if you use
your own unique name.

##### Docker Configuration & Customizations
Depending on which operation system you're running docker desktop on you may 
need to set the resources docker desktop can use. You can click on the Docker
icon in your system tray and open the dashboard. Click on the gear icon for
settings. If you see a Resources tab, you maybe able to increase the limits on
your docker desktop program. If you don't see this setting the defaults should 
be more than enough to handle everything.

Additionally in this configuration file, there is a file called 
`docker-compose.yml`. This file is a configration file for your server. For 
the most part you should not edit this file unless you know what you're doing
and since everything is running locally, security such as passwords and salt
phrases are not important. But for added security you can change some values.

These are
* HOST
* DATABASE_PORT
* MYSQL_ROOT_USER
* MYSQL_ROOT_PASSWORD
* SALT_PHRASE
* DEFAULT_USER

found in the `environment` section. Please note that spaces are important in
this file and that you should not change the indentation levels. 

The values are found immedately after the `:` for the above 3 keys and can be 
whatever you want, but should avoid spaces.

Note, `MYSQL_ROOT_USER`, `MYSQL_ROOT_PASSWORD`, and `DATABASE_PORT` are ignored
if the value of `HOST` is `sqlite`. 

`sqlite` is a light weight database provided to allow running the program without
additional software running. However as the name implies the database is a little
simple. If you have a mysql database it's recommended that you point the `HOST`, 
`DATABASE_PORT`, and other `MYSQL_` related variables at your database. If left
alone your database will be created in the `/config/mysql-data` folder as a `*.db`
file.

If you're going to change `SALT_PHRASE` or `DEFAULT_USER` **do it now**. See 
[Virtual Machine Setup](#Virtual-Machine-Setup) for more information.

Variables that start with `APACHE_` should be left alone as they are needed to 
make the webservice run.

Finally, the `ports` section map the outside (host) port to a inside (container)
port. This just means if you want to view the website you need to add the host
port to the url, like `http://localhost:1086/`. If 1086 is already being used 
on your computer for some other service, please modify the number that comes 
before the colon (`1086:80`) to whatever you like. 

##### Starting the server
Once you have installed Docker Desktop and extracted or downloaded the code to
a folder on your computer somewhere, all you need to do is start the server to
begin hosting the program website on your local machine. 

For the examples below I'll assume you extracted or saved the file to your
`Desktop` in a folder called `iris`. The file `docker-compose.yml` should be 
in the `iris` folder, if this is not where your files are then please change 
the `cd somePathHere` part of the example below to wherever your 
`docker-compose.yml` file - and all the other files - are.

Using `git` the command to do that is as follows

Windows (open command by pressing `Windows Key + R` and typing cmd):
```
cd %USERPROFILE%\Desktop\iris
docker-compose up
```
Windows Powershell OR 
Mac (open terminal by pressing `command + spacebar` and typing 
`terminal.app`):
```
cd ~/Desktop/iris
docker-compose up
```

The first time you run this command it will download all the resources
and build your virtual machine. This can take a while and will take 
some disk space up at this time, please be sure to be connected to the 
internet.

After this is complete your terminal or shell window should display some
information about what's going on in the virtual machine. There shouldn't
be any major errors in the window if all went well. **Do not close** this 
window while you are using this program as it will also cause the servers 
to stop. 

You should now be able to access your local web server at the url
[http://localhost:1086/](http://localhost:1086/). localhost should be 
defined by default.

On subsequent server start ups, you can add `-d` to the end of the 
`docker-compose up -d` line in the above comands if you are not interested
in the messages and/or want to be able to close the terminal window. You
can start your server with the `-d` the first time as well but it
is not recommended as you will not see the progress of your downloads.

Please note that if you start the server with the `-d` option then you will
need to stop the server. Closing the terminal will no longer be enough to 
stop the server.

##### Stopping the server
If you have the terminal window open and see all the logs on the screen,
then you can stop the server by pressing `ctrl+c` to trigger a shut down.
Try to resist shutting down immedately by pressing `ctrl+c` again or by 
closing the window.

If you have closed the window and started the server using the `-d` option. 
You will need to tell docker to close the background task. 

Windows (open command if it is not already open by pressing 
`Windows Key + R` and typing cmd):
```
cd %USERPROFILE%\Desktop\iris
docker-compose down
```
Windows Powershell OR 
Mac (open terminal if it is not already open by pressing 
`command + spacebar` and typing `terminal.app`):
```
cd ~/Desktop/iris
docker-compose down
```

Again you should see the same message about stopping the servers.

### What's going on
Docker is a virtualization tool that allows you to run a computer inside
a computer. The compose file is a set of configurations and setups to create
those computers in a specific way so that they can do some tasks.

This computer is running on a version of linux and does everything it needs 
to host or connect to a database and run a web server.

##### Virtual Machine Setup
It was briefly described earlier, but you can do some custiomizations on
your setup based on your level of understanding with docker and 
docker-compose. Nothing needs to be done and it will work as is.

With that said the main things that can safely be changed are found in
`docker-compose.yml` 

These are
* SALT_PHRASE
* DEFAULT_USER

To change these values modify the text that comes after the `:` and 
don't use spaces, and don't change the indentation. 

For example
Before
```
      MYSQL_ROOT_PASSWORD: secret
```
After
```
      MYSQL_ROOT_PASSWORD: superSecret
```
**SALT_PHRASE** This is a secret that is used to make decoding your 
passwords even harder. While all passwords are encrypted in a one way
hash so that they cannot be decrypted. There is something known as rainbow
tables that exist. In short it's a list of if you see  this hash in a 
database, then it's probably this plain text value for a password list.

Adding what is known as a salt to the password makes those rainbow tables
meaningless, unless they use the same salt in the same way that this program
does. Basically it's like (password + salt) hashed to make the password in 
your database. This way if someone dumps your data they can't easily figure
out what your passwords were. **IMPORTANT** once you start the program for 
the first time **you should not change this value** doing so will make all
your passwords invalid. You will need to somehow clear the values and set
new passwords again.

**DEFAULT_USER** This is only used in the first login. This is the user that
has all the power in the program. Also the user that you can first log in as.
Typically this is your username you want to log in as. You can run programs
as this user locally if you want.

##### Volumes
Virtual machines are like computers inside of computers. Just like your 
computer has a c: or primary hard drive with files and folders, they each
have their own files and folders that are not the same as your files and 
folders. The exceptions are the volumes. 

Volumes are like saying, I am giving access to my computers files to the 
virtual machine, and that virtual machine can access and modify those files
and folders on my computer. 

An example is
```
    volumes: 
      - "./config/images:/var/www/html/images/studies"
```
means, on my computer give access to the folder `/config/images`  and
all of the files and subfolders in it as well, to the machine(s) this 
definition is on at the location `/var/www/html/images/studies` on that 
virtual machine. This way your data is safely saved to your local machine 
when you restart the server.

Why am I telling you about volumes? There is a shortcut to upload/download
images to and from your study. You can use the `manage studies` menu and click
`Upload Images to your study` as you would normally on the web. But they 
ultimaetly drop those images into the `current folder` in the subfolder 
`images/structures/someNumber/` where `someNumber` represents the number in
the database for your study. So if you followed this guide and extracted your
files to the Desktop it would be found at 
`Desktop/iris/config/images/structures/1/` as an example.

How do I figure out that `someNumber`? The easiest way is upload a single 
image using the web ui to create the correct folder, then drag and drop all 
the other images into that folder on your computer. 

### Why is there a standalone version
This applcation was specifcally designed to run on a local machine set up
manually on a local webserver and was not origianlly intended to be used 
online. As there was a request to make the program accessible online some
very rapid development went into creating users and management tools to do 
the most basic of admin tasks. But the first use case was to have a single 
study with a single program admin.

So what's the problem? Confidentialiaty. Which leads us to some 
[Privacy concerns](#Privacy-concerns)

##### Privacy concerns
As the data you upload is highly sensative information, you might have some
reservations about uploading them to a random website somewhere. I as the
site owner will always be able to access your data. And my webhost, as the 
service provider of the website, will also technically have access to your 
files. The webhost has their own privacy policy and they should not ever
access the data on this site, but that doesn't mean they don't have the 
ability to.

Also, you may trust me as the developer, but all you have from me is my own 
word that I'm not doing anything to your data.

By having your program hosted locally you can disconnect from the internet
once the program is built the first time and since all the files are running 
off your machine, your data is as safe as your machine is.

##### Updates
This program is probably not going to get a lot of updates but when they do 
happen there wont be any notifications. Also updating should not be necessary
unless there's a specific issue you are having and hopefully you'd be asking
me to do that for you.

With that said, if you installed the program using the `git` method in the
[Downloading this code](#Downloading-this-code) section. There is a simple
command to downlad the latest code. Please note that if you made any changes 
to the `docker-compose.yml` file you should first back up that file first.

Once you have backed up the changes, in your command/terminal window type

Windows (open command if it is not already open by pressing 
`Windows Key + R` and typing cmd):
```
cd %USERPROFILE%\Desktop\iris
git pull origin master
```
Windows Powershell OR 
Mac (open terminal if it is not already open by pressing 
`command + spacebar` and typing `terminal.app`):
```
cd ~/Desktop/iris
git pull origin master
```

### Troubleshooting
##### Quick Tips
The program was originally created to handle a single study at a time and as
such several features were added on as a way to allow other researches to 
use the same categorization system. This resulted in a bit of quirky and 
clumbsy interface. When in the admin section please be sure to check the name
of your study at the top of the page, to make sure you're administering the 
correct study. 

When a user is created they are added without a password. On the first login
the user will be asked to create their first password but if they have not
created a password this will give anyone access to the study if they can 
guess that users' username. Please be sure to have users set up a password
right away. Or set one yourself manually and have the user change their 
password after logging in the first time.

##### Upload shortcuts
Every study you create will get an ID Number. This ID will tell the program
where the files reside on your system. For images you can find them in 
`config/images/structures` in a folder that matches your ID Number.

If you want to quickly upload a lot of images without going through the admin
page uploader, you can drag and drop images into this folder and reindex them
in the admin page after you're done.

##### Reset everything
There are 2 ares where data is persisted in the program on your local machine: 
`config/images` and `config/mysql-data`.

If you find yourself in a situation where you want to start all over from 
scratch you can delete/move the files in these 2 folders. Please leave 
`config/images/structures/samples/Blue-Iris.jpg` and any `.blank` files.

If that fails you can re-download/clone the repository from source again 
as described in the  [Installation](#Installation) section of this guide.