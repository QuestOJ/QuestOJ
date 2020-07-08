GOCMD=go
GOBUILD=$(GOCMD) build
GOCLEAN=$(GOCMD) clean
GOTEST=$(GOCMD) test
GOGET=$(GOCMD) get
BINARY_NAME=codeforces
BINARY_UNIX=$(BINARY_NAME)_unix
BUILD_ENV = env

all: deps build

build: build-linux64 build-linux32
clean:
	$(GOCLEAN)
	rm -f $(BINARY_NAME)
	rm -f $(BINARY_UNIX)
run:
	$(GOBUILD) -o $(BINARY_NAME) -v ./...
	./$(BINARY_NAME)
deps:
	$(GOGET) github.com/shirou/gopsutil/process

build-linux64:
	mkdir -p build/linux_64
	${BUILD_ENV} GOARCH=amd64 GOOS=linux ${GOBUILD} -o build/linux_64/${BINARY_NAME} -v

build-linux32:
	mkdir -p build/linux_32
	${BUILD_ENV} GOARCH=386 GOOS=linux ${GOBUILD} -o build/linux_32/${BINARY_NAME} -v
