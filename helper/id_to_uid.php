<?php

function customEncode(userId) {
  return (userId * 123 + 456).toString();
}

const userId = 123;
const encodedUserId = customEncode(userId);
console.log(encodedUserId); // '15177'

function customDecode(encodedUserId) {
  return (parseInt(encodedUserId) - 456) / 123;
}

const decodedUserId = customDecode(encodedUserId);
console.log(decodedUserId); // 123

?>