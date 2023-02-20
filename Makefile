release:
	ncc build --config="release"

install:
	ncc package install --package="build/release/com.netkas.markinphant.ncc" --skip-dependencies --reinstall -y

uninstall:
	ncc package uninstall -y --package="com.netkas.markinphant"