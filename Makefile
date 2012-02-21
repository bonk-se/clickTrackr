#!/bin/make
#
# Compresses the clickTrackr source (using Clouser Compiler)
#
# HOWTO:
# Just run "make" in this directory when source has been modified
#
JSC = java -jar build/closure-compiler.jar --compilation_level SIMPLE_OPTIMIZATIONS

# targets for auto compression
# One name per line with \ at the end of line to be nice to our version control
TARGETS = \
	clickTrackr.js \

# default target
all: $(TARGETS)

.PHONY: clean list-targets

clean:
	rm -f $(TARGETS)

list-targets:
	echo $(TARGETS)

# implicit targets
%.js: src/%.js
	$(JSC) --js="$<" --js_output_file="$@"
