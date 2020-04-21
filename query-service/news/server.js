String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};

// Load environment configuration with dotenv
var results = require('dotenv').config({
  path: `.env.${process.env.NODE_ENV}`
})
if (results.error) {
  throw results.error
}

var envConfig = results.parsed

var grpc = require('grpc')
var rr = require('rr')
var newssProto = grpc.load('protos/news.proto')
var querystring = require('querystring')
var axios = require('axios')
var https = require('https')

const service = axios.create({
  baseURL: envConfig.NEWS_URI,
  timeout: 300,
  httpsAgent: new https.Agent({
    rejectUnauthorized: false
  }),
  withCredentials: true
})

var server = new grpc.Server()

server.addService(newssProto.ollinews.NewsService.service, {
  queryNewss: function(call, callback) {
    var data = []
    var sqlString = 'SELECT * FROM olli_news'
    // var orderString = 'ORDER BY countlisten DESC'
    var orderString = 'ORDER BY datepublished DESC'
    var whereString = 'MATCH(\''

    if (call.request.keyword.length > 0) {
      whereString += call.request.keyword
    }

    whereString += '\')'

    sqlString += ' WHERE ' + whereString + ' '+ orderString +' LIMIT 5 OPTION ranker=sph04, max_matches=5, field_weights=(title=10, keywords=30)'

    var mysql = require('promise-mysql')
    var connection
    mysql
      .createConnection({
        host: envConfig.SPHINX_HOST,
        port: envConfig.SPHINX_PORT
      })
      .then(function(conn) {
        connection = conn
        return connection.query(sqlString)
      })
      .then(function(rows) {
        if (rows.length > 0) {
          for (var i = 0, len = rows.length; i < len; i++) {
            var news = rows[i]

            data.push({
              nid: news.nid,
              description: news.description
            })
          }
        }

        connection.end()
        callback(null, data)
      })
  }
})

server.bind('0.0.0.0:' + envConfig.QUERY_SERVICE_PORT, grpc.ServerCredentials.createInsecure())

console.log('Server running at http://0.0.0.0:' + envConfig.QUERY_SERVICE_PORT)

server.start()
