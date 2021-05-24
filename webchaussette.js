function WCCClientMain() {
  try {

    // Create the request
    var form = document.createElement("form");
    form.setAttribute("method", "post");
    var action = document.createElement("input");
    action.setAttribute("type", "text");
    action.setAttribute("name", "action");
    action.setAttribute("value", "measures");
    form.appendChild(action);
    var project = document.createElement("input");
    project.setAttribute("type", "text");
    project.setAttribute("name", "project");
    project.setAttribute("value", $("#selProject option:selected").html());
    form.appendChild(project);
    var last = document.createElement("input");
    last.setAttribute("type", "text");
    last.setAttribute("name", "last");
    last.setAttribute("value", "100");
    form.appendChild(last);

    // Send the request
    HTTPPostRequest("./api.php", form, UpdateData);

  } catch (err) {
    console.log(err.stack);
  }
}

function HTTPPostRequest(url, form, handler) {
  try {

    // Create the request object
    if (window.XMLHttpRequest) {
      var xhr = new XMLHttpRequest();
    } else {
      var xhr = new ActiveXObject('Microsoft.XMLHTTP');
    }

    // Hook for the reply
    xhr._handler = handler;
    xhr.onreadystatechange = function() {

      // If the request is ready
      if (this.readyState == 4) {
        if (this.status == 200) {

          // The request was successful, return the reply data
          var returnedData = {};
          try {
            returnedData = JSON.parse(this.responseText);
          } catch(err) {
            // Remove the displayed data
            $("#divData").empty();
            console.log(this.responseText);
            returnedData = JSON.parse('{"err":"JSON.parse failed."}');
          }

        } else {

          // Remove the displayed data
          $("#divData").empty();

          // The request failed, return error as JSON
          var returnedData = 
            JSON.parse('{"err":"HTTPRequest failed : ' + 
              this.status + '"}');

        }

        this._handler(returnedData);

      } else {

        // Remove the displayed data
        $("#divData").empty();

      }

    };

    // Send the request
    xhr.open("POST",url);
    var formData = new FormData(form);
    xhr.send(formData);

  } catch (err) {
    console.log(err.stack);
  }

}

function UpdateData(ret) {
  try {

    // If the request was successful
    if (ret["ret"] == "0") {

      // Create a table containing the data
      var tableData = "";
      tableData += "<table>";
      tableData += "<tr>";
      // Metric labels in the header
      for (label in ret["labels"]) {
        tableData += "<th>";
        tableData += ret["labels"][label];
        tableData += "</th>";
      }
      tableData += "</tr>";
      // Loop on measures
      for (measure in ret["values"]) {
        tableData += "<tr>";
        // Loop on metrics in the measure
        for (value in ret["values"][measure]) {
          tableData += "<td>";
          tableData += ret["values"][measure][value];
          tableData += "</td>";
        }
        tableData += "</tr>";
      }
      
      tableData += "</table>";

      // Set the data in divData
      $("#divData").html(tableData);

    } else {

      // Remove the displayed data
      $("#divData").empty();

    }

  } catch (err) {
    console.log(err.stack);
  }

}

// ------------ WCClient class

function WCClient() {
  try {

    this.prop = "";

  } catch (err) {
    console.log(err.stack);
  }
}

WCClient.prototype.Login = function() {
  try {

  } catch (err) {
    console.log(err.stack);
  }
}
