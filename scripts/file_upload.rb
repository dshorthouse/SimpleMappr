#!/usr/bin/env ruby

require 'find'
require 'rest-client'
require 'json'
require 'open-uri'

class SimpleMappr

  def initialize
    set_output_directory
    set_working_directory
    make_maps
  end

  def set_output_directory
    @output_directory = ARGV[1] || File.dirname(__FILE__)
    Dir.mkdir(@output_directory) unless File.exists?(@output_directory)
  end

  def set_working_directory
    directory = ARGV[0] || File.dirname(__FILE__)
    Dir.chdir(directory)
  end

  def make_maps
    Find.find(".").each do |f|
      next if IO.popen(["file", "--brief", "--mime-type", f], in: :close, err: :close).read.chomp != "text/plain"
      params = { :file => File.new(f, 'r') }
      RestClient.post('http://www.simplemappr.net/api/', params) do |response, request, result, &block|
        r = JSON.parse(response.body, :symbolize_names => true)
        output_image = File.basename(f).sub(File.extname(f), File.extname(r[:imageURL]))
        open(File.join(@output_directory, output_image),'wb') do |image|
          image << open(r[:imageURL]).read
        end
      end
    end
  end

end

SimpleMappr.new

# Usage
# $ ./application.rb "/Users/dshorthouse/Desktop" "/Users/dshorthouse/Desktop/maps"