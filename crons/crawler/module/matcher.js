

let matcher = {};

matcher.match = function(url, source_html, pattern) {
  let result = source_html.match(/pattern/g);
  return result;
}

matcher.match_all = function(html, regexp) {
  let matched_array = [];
  while ((regexp.exec(html)) != null) {
    matched_array.push(RegExp.$1);
  }

  return matched_array;
}

module.exports = matcher;
