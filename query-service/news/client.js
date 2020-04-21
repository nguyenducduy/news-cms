// Load environment configuration with dotenv
var results = require('dotenv').config({
  path: `.env.${process.env.NODE_ENV}`
})
if (results.error) {
  throw results.error
}

var envConfig = results.parsed
var grpc = require('grpc')
var newssProto = grpc.load('protos/news.proto')

var client = new newssProto.ollinews.NewsService(
  envConfig.QUERY_SERVICE_HOST + ':' + envConfig.QUERY_SERVICE_PORT,
  grpc.credentials.createInsecure()
)

function printResponse(error, response) {
  if (error) console.log('Error: ', error)
  else console.log(response)
}

client.queryNewss(
  {
    keyword: 'Salim'
  },
  function(error, data) {
    printResponse(error, data)
  }
)
