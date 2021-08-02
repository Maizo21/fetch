/**
 * Funcion para hacer solicitudes. Requiere de la url a conectarse y en caso de envio o modificacion de data se debe indicar el objeto.
 * @param {string} url url a conectarse.
 * @param {object} data objeto a enviar/modificar/eliminar.
 */
var datajson;

function query(url, data = undefined) {
  data = {
    task: "consulta json",
  };
  fetch(
    url,
    data === undefined
      ? {}
      : {
          method: "POST",
          body: JSON.stringify(data),
          headers: {
            "Content-Type": "application/json",
          },
        }
  )
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      datajson = data;
      result(data);
    })
    .catch(function (error) {
      console.log(error);
    });
}

query("../fetch/php/test.php");

function result(json) {
  console.log(json);
}

// Build formData object.
let formData = new FormData();
formData.append("task", "Consult");

function queryForm(url) {
  fetch(url, {
    body: formData,
    method: "post",
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      datajson = data;
      result(data);
    })
    .catch(function (error) {
      console.log(error);
    });
}

function queryWebComponent(url, callback) {
  return new Promise((res, rej) => {
    fetch(url, {
      method: "post",
      body: formData,
    })
      .then((data) => data.json())
      .then((json) => {
        console.log(json);
        callback(json);
      })
      .catch((error) => rej(error));
  });
}
queryWebComponent("../fetch/php/test.php", result2);

function result2(json) {
  console.log(json);
}
