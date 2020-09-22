/*
    Copyright 2013-2014 appPlant UG

    Licensed to the Apache Software Foundation (ASF) under one
    or more contributor license agreements.  See the NOTICE file
    distributed with this work for additional information
    regarding copyright ownership.  The ASF licenses this file
    to you under the Apache License, Version 2.0 (the
    "License"); you may not use this file except in compliance
    with the License.  You may obtain a copy of the License at

     http://www.apache.org/licenses/LICENSE-2.0

    Unless required by applicable law or agreed to in writing,
    software distributed under the License is distributed on an
    "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
    KIND, either express or implied.  See the License for the
    specific language governing permissions and limitations
    under the License.
 */

package de.appplant.cordova.plugin.postrq;

import org.apache.cordova.CordovaPlugin;
import org.apache.cordova.CallbackContext;


// Post
import java.io.*;
import java.net.*;
import java.util.*;
// Post end

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

/*
*   TODO:
*   - URL *
*   - POST / GET *
*   - Values
*   - Custom headers
*
* */
public class postrqplugin extends CordovaPlugin {

    @Override
    public boolean execute(String action, JSONArray args, CallbackContext callbackContext) throws JSONException {
        if (action.equals("login")) {
            String urlString = args.getString(0); // Vilken url som helst
            //String urlParameters = args.getString(1); // Vilket data som helst
            //String urlMethod = args.getString(2);
            //String password = args.getString(1);
            String resultString = "knas";
            //String url = "https://satans-demokrati-72.herokuapp.com/login";
            //"https://satans-demokrati-72.herokuapp.com/login"
            /* POST REQUEST */

            String urlParameters = args.getString(1);


            try {

                //URL url = new URL(urlString);
                //HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                //conn.setReadTimeout(15000);
                //conn.setConnectTimeout(15000);
                //conn.setRequestMethod("POST");
                //conn.setDoOutput(true);
               // conn.setRequestProperty("User-Agent","Mozilla/5.0 ( compatible ) ");
                //conn.setRequestProperty("Accept","*/*");


                //String urlParameters = "name=322&password=322";
                URL url = new URL(urlString);
                URLConnection conn = url.openConnection();


                conn.setDoOutput(true);

                OutputStreamWriter writer = new OutputStreamWriter(conn.getOutputStream());

                writer.write(urlParameters);
                writer.flush();

                String line;
                BufferedReader reader = new BufferedReader(new InputStreamReader(conn.getInputStream()));

                while ((line = reader.readLine()) != null) {
                    resultString = line;
                }

                writer.close();
                reader.close();

            } catch (IOException e) {
                this.echo("fail", callbackContext);
                throw new RuntimeException(e);
            }




            /*Post request end */

            // return values
            this.echo(resultString, callbackContext);
            return true;
        }
        return false;
    }

    private void echo(String message, CallbackContext callbackContext) {
        if (message != null && message.length() > 0) {
            callbackContext.success(message);
        } else {
            callbackContext.error("ngt");
        }
    }
}
