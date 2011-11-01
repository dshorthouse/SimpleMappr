#!/usr/bin/env ruby

require 'find'

system(`rm -f template.po`)
po_file = File.open("template.po", 'a')

excludes = [".svn", "lib", "modules", "feathers"]
exclude_files = []
strings = []
lines = []
output = ""
Find.find(".") do |path|
  if FileTest.directory?(path)
    if excludes.include?(File.basename(path))
      Find.prune
    else
      next
    end
  else
    filename = File.basename(path)
    if filename =~ /\.php/ and not exclude_files.include?(filename)
      cleaned = path.sub("./", "")
      contents = File.read(path)
      if contents =~ /_e\(['|"](.*?)['|"], ['|"]twollars['|"]/
        counter = 1
        File.open(path, "r") do |infile|
          while (line = infile.gets)
            line.gsub(/_e\(['|"](.*?)['|"], ['|"]twollars['|"]/) do
              text = $1
              unless strings.include?(text)
                output << '#: '+cleaned+':'+counter.to_s+"\n"
                output << 'msgid "'+text+'"'+"\n"
                output << 'msgstr ""'+"\n\n"
                strings << text
              else
                unless lines.include?(cleaned+":"+counter.to_s)
                  output = output.gsub("msgid \""+text+"\"\nmsgstr \"\"\n\n", 
                                       "#: "+cleaned+":"+counter.to_s+"\nmsgid \""+text+"\"\nmsgstr \"\"\n\n")
                end
              end
            end
            counter = counter + 1
          end
        end
      end
    end
  end
end
po_file.puts '# Chyrp Translation File.'
po_file.puts '# Copyright (C) 2007 Alex Suraci'
po_file.puts '# This file is distributed under the same license as the Chyrp package.'
po_file.puts '# Alex Suraci <(snipped)>, 2007.'
po_file.puts '#'
po_file.puts '#, fuzzy'
po_file.puts 'msgid ""'
po_file.puts 'msgstr ""'
po_file.puts '"Project-Id-Version: Chyrp v1.0 Beta\n"'
po_file.puts '"Report-Msgid-Bugs-To: (snipped)\n"'
po_file.puts '"POT-Creation-Date: 2007-08-03 00:29-0500\n"'
po_file.puts '"PO-Revision-Date: '+Time.now.strftime("%Y-%m-%d %H:%M")+'-0500\n"'
po_file.puts '"Last-Translator: Alex Suraci <(snipped)>\n"'
po_file.puts '"Language-Team: English (en) <(snipped)>\n"'
po_file.puts '"MIME-Version: 1.0\n"'
po_file.puts '"Content-Type: text/plain; charset=UTF-8\n"'
po_file.puts '"Content-Transfer-Encoding: 8bit\n"'
po_file.puts ''
po_file.puts output