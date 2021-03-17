

function  saveChanges (d) {
    var data = (d.entity_id).concat("-").concat(d.x/width).concat("-").concat(d.y/height)

    let url = "http://localhost:8888/d3-erd/erd_api.php?data="+data;

    var xhttp = new XMLHttpRequest();
    xhttp.open("POST", url, true);
    xhttp.onreadystatechange = function () {//Call a function when the state changes.

        if (this.readyState === 4 && this.status === 200) {
            var rt = xhttp.responseText;
            console.log(xhttp.responseText)
        }
    }
    return xhttp.send();
}
function getJsonData(callback){

    if(!callback){
        d3.json('data/sample_data.json',function(data){
            load(data);
        })
    }

    let url = "http://localhost:8888/d3-erd/erd_api.php?v=dev"

    var xhttp = new XMLHttpRequest();

    xhttp.open("GET", url, true);
    xhttp.onreadystatechange = function () {//Call a function when the state changes.

        if (this.readyState === 4 && this.status === 200 ) {
            var rt = xhttp.responseText;
            let jData = JSON.parse(xhttp.responseText)
            console.log(jData)
            if(callback) {load(jData)}
        }
    }
    xhttp.send();
}

// call getJsonData(callback) to use the api to retrieve the ER data from a database
// otherwise load the local json sample data

setTimeout(function(){
    getJsonData() // loads data/sample_data.json
    //getJsonData(load) // load data from database
},100)