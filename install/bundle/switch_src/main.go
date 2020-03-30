package main

import (
	"bytes"
	"encoding/binary"
	"fmt"
	"net"
	"os"
	"time"
)

type ICMP struct {
	Type        uint8
	Code        uint8
	CheckSum    uint16
	Identifier  uint16
	SequenceNum uint16
}

func usage() {
	msg := `
Need to run as root!
Usage:
	goping host
	Example: ./goping www.baidu.com`
	fmt.Println(msg)
	os.Exit(0)
}
func getICMP(seq uint16) ICMP {
	icmp := ICMP{
		Type:        8,
		Code:        0,
		CheckSum:    0,
		Identifier:  0,
		SequenceNum: seq,
	}
	var buffer bytes.Buffer
	binary.Write(&buffer, binary.BigEndian, icmp)
	icmp.CheckSum = CheckSum(buffer.Bytes())
	buffer.Reset()
	return icmp
}
func sendICMPRequest(icmp ICMP, destAddr *net.IPAddr) (int64, error) {
	conn, err := net.DialIP("ip4:icmp", nil, destAddr)
	if err != nil {
		fmt.Printf("Fail to connect to remote host: %s\n", err)
		return -1, err
	}
	defer conn.Close()
	var buffer bytes.Buffer
	binary.Write(&buffer, binary.BigEndian, icmp)
	if _, err := conn.Write(buffer.Bytes()); err != nil {
		return -1, err
	}
	tStart := time.Now()
	conn.SetReadDeadline((time.Now().Add(time.Second * 2)))
	recv := make([]byte, 1024)
	_, err = conn.Read(recv)
	if err != nil {
		return -1, err
	}
	tEnd := time.Now()
	duration := tEnd.Sub(tStart).Nanoseconds() / 1e6
	return duration, err
}
func CheckSum(data []byte) uint16 {
	var (
		sum    uint32
		length int = len(data)
		index  int
	)
	for length > 1 {
		sum += uint32(data[index])<<8 + uint32(data[index+1])
		index += 2
		length -= 2
	}
	if length > 0 {
		sum += uint32(data[index])
	}
	sum += (sum >> 16)
	return uint16(^sum)
}
func main() {
	raddr1, _ := net.ResolveIPAddr("ip", "gitee.com")
	raddr2, _ := net.ResolveIPAddr("ip", "github.com")

	var ping_gitee int64 = 0
	var ping_github int64 = 0

	for i := 1; i <= 3; i++ {
		res, err := sendICMPRequest(getICMP(uint16(i)), raddr1)

		if err == nil && res != -1 {
			ping_gitee = ping_gitee + res
		} else {
			ping_gitee = ping_gitee + 1000
		}
	}

	for i := 1; i <= 3; i++ {
		res, err := sendICMPRequest(getICMP(uint16(i)), raddr2)

		if err == nil && res != -1 {
			ping_github = ping_github + res
		} else {
			ping_github = ping_github + 1000
		}
	}

	if ping_gitee > ping_github {
		fmt.Println("github")
	} else {
		fmt.Println("gitee")
	}
}
