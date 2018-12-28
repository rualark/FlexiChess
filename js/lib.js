function findObjectByKey(array, key, value) {
  for (let i = 0; i < array.length; i++) {
    if (array[i][key] === value) {
      return array[i];
    }
  }
  return null;
}

function countObjectsByKey(array, key, value) {
  let count = 0;
  for (let i = 0; i < array.length; i++) {
    if (array[i][key] === value) {
      ++count;
    }
  }
  return count;
}

function first(p) {
  for (let i in p) {
    return p[i];
  }
}

function amin(arr) {
  let min_val = first(arr);
  arr.forEach(function(item, i, arr) {
    if (item < min_val) {
      min_val = item;
    }
  });
}

function amax(arr) {
  let max_val = first(arr);
  arr.forEach(function(item, i, arr) {
    if (item > max_val) {
      max_val = item;
    }
  });
}