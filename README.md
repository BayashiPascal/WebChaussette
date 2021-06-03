# WebChaussette

WebChaussette, the poor man's WebSocket.

WebChaussette is a demo/boilerplate of an equivalent to WebSocket using vanilla JavaScript and PHP only. As an example, it works as a basic online chat web app.

## 1 Usage

1. Copy the files in this repository on your web server
2. Access server.php, click on "create" to get a new session and its key
3. Ask the users to access client.php, give them the session's key, ask them to request a connection by clicking on "Request connection" after filling the key and name
4. On the server.php page, accept the connection requests
5. On the client.php pages, send messages and have fun with your friends
6. At the end of the session, on the server.php page, click "close" to end the session and disconnect the users

## 2 How it works

WebChaussette is based on HTTP requests and a SQlite database.

The client pings the API every few seconds to request or send new data. The API stores data into the database, and keeps track of the sender and receivers. The data are encoded into JSON and the API logic is independent of their content. It is the clients who process the data content.

In that example, a connection request phase, managed through the server.php page, is implemented, and data are broadcasted toward all connected users of the same session. It would be easy to modify the code to let the API automatically manage the connection requests instead of server.php, and add a receiver field to the `sendData` request to filter the broadcasting.

Compare with WebSocket, the advantages of WebChaussete are:
* easy to deploy, just copy the files (less than 30k non-minimized) on a web server and open the web pages in a browser, no need to install X megabyte of frameworks running in layers of virtual environment and messing up with the web server parameters to enable TCP on the server side
* easy to implement, this demo is just around 1000 lines of vanilla php and javascript, no need to code the server side in another language or have it works through another framework on the web server
* straightforward to implement broadcast to all users while WebSocket cannot
the disadvantages are:
* less reactive, new data are received at a periodic interval, while in WebSocket they are received as soon as they are sent
* doesn't scale up well, the client pings the API periodically even if there are no new data, and http requests are certainly heavier than TCP data packets; also, on a web server with very low spec, a lot of clients pinging at high frequency is surely not recommendable
* bias toward textual data, binary data would require some sort of encoding like base64 while it's straightforward with WebSocket
* no particluar security implemented in this demo, if you plan to use it seriously you should add at east a proper login on the server.php page, proper identification of the users, and any other measure according to your security policy
* the name is a terrible joke, yes, sorry

## 3 License

WebChaussette, the poor man's WebSocket.
Copyright (C) 2021  Pascal Baillehache

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.

