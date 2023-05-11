const log4js = require('log4js');
const path = require('path');

const logpath = path.resolve(__dirname, '../../log/esacs.com.js.log');

let logger = log4js.getLogger();

log4js.configure({
  appenders: {
    out: { type: 'stdout' },
    app: { type: 'dateFile', filename: logpath, backups: 14, "pattern": "-yyyy-MM-dd" }
  },
  categories: {
    default: { appenders: ['out', 'app'], level: 'all' },
  }
});

module.exports = logger;
