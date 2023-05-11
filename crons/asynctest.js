
(async()=> {
  for (let index = 3; index < 7; index++) {
    if (index == 5) {
      console.log("555555");
      continue;
    }
    await mysleep(index);
  }
})();

let result =  mysleep(1);


async function mysleep(time) {
  await sleep(time * 1000);
  await console.log(`sleep: ${time}`);
  console.log('finished');

  return time;
}

async function sleep(waitMsec) {
  var startMsec = new Date();

  // 指定ミリ秒間だけループさせる（CPUは常にビジー状態）
  while (new Date() - startMsec < waitMsec);
}
