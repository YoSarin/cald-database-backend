docker build -t kacerr/cald_backend:`git rev-parse HEAD` .
docker push kacerr/cald_backend:`git rev-parse HEAD`
echo kacerr/cald_backend:`git rev-parse HEAD`
