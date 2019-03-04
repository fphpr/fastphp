
  function getJson(str){
    str = str.replace(/\n/g, '\\n');
    str=str.replace(/\r/g, '\\r');
    return JSON.parse(str);
  }
