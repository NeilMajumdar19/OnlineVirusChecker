# OnlineVirusChecker

Webpage:
Ensures a secure Session mechanism.
Allows the user to submit a putative infected file and shows if it is infected or not.
Lets authenticate an Admin and allows him/her to submit a Malware file, plus the name of the uploaded Malware.
When an Admin adds the name of a malware during the uploading of a Malware file, it ensures that the string contains only English letters (capitalized or not) and digits. Any other character, or an empty string, must be avoided. 

Web Application:
Reads the file in input, per bytes, and, if it is Malware, stores the sequence of bytes, say, the first 20 bytes (signature) of the file, in a database (Note: only an Admin can upload a Malware file).
Reads the file in input, per bytes, and, if it is a putative infected file, searches within the file for one of the strings stored in the database (Note: a normal user will always upload putative infected files).

MySQL Database:
Stores the information regarding the infected files in input, such as name of the malware (not the name of the file) and the sequence of bytes.
Stores the information related to the Admin with username and password.
Stores the information related to Users with username and password.
