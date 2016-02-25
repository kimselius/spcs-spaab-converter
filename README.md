Converter for pensionsvalet
=============

### What it is and what it can be used for ###

This script allows you to export files from Visma lön and then convert them into a file format that is recognized by Swedish pensionsvalet.

It's used for exporting and converting payroll data to be sent into pensionsvalet system for calculating pension contribution.

### How to use this script ###

Upload it to a web server with PHP 5 installed and navigate to the base directory.
You will see a page where you can upload your Visma file and enter additional data. Once you hit submit the script will generate a new export file that you can download to your computer.

This script places files temporarily in the /tmp/ directory, but it stores nothing.

### License ###
This content is released under the (http://opensource.org/licenses/MIT) MIT License.