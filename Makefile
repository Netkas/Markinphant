build:
	ncc build --config="release" --log-level debug

install:
	sudo ncc package install --package="build/release/com.netkas.markinphant.ncc" --skip-dependencies --reinstall -y --log-level debug

clean:
	rm -rf build

config:
	configlib --conf markinphant --editor nano

run:
	markinphant --log-level debug