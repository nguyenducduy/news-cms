// Load environment configuration with dotenv
var results = require('dotenv').config({
  path: `.env.${process.env.NODE_ENV}`
})
if (results.error) {
  throw results.error
}

var envConfig = results.parsed
var grpc = require('grpc')
var songsProto = grpc.load('protos/songs.proto')

var client = new songsProto.olliplaymusic.MusicService(
  envConfig.QUERY_SERVICE_HOST + ':' + envConfig.QUERY_SERVICE_PORT,
  grpc.credentials.createInsecure()
)

function printResponse(error, response) {
  if (error) console.log('Error: ', error)
  else console.log(response)
}

client.querySongs(
  {
    name: 'đường đến đỉnh vinh quang',
    artist: ''
  },
  function(error, songs) {
    printResponse(error, songs)
  }
)
