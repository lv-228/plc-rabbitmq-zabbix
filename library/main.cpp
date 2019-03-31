#include <cstdint>
#include <phpcpp.h>
#include <modbus.h>
#include <string>
#include <stdlib.h>

uint16_t ports[5] = {500,501,502,503,102};
Php::Value php_buff;

std::string f(uint16_t) { return "ui16";}

using namespace std;

modbus get_modbus_connect(string address, int num_port, int slave_id)
{
	modbus mb = modbus(address,ports[num_port]);
	mb.modbus_set_slave_id(slave_id);
	mb.modbus_connect();
	return mb;
}

Php::Value modbus_logo8_get_bit_by_type_name(Php::Parameters &params)
{
	bool *buff = new bool[(int)params[3]];
	//Чтение дискретного входа
	if(params[4] == "DI" || params[4] == "I")
	{
		if(params[2] + params[3] > 24)
		{
			Php::out << "Ошибка! Адресс регистра DI не может быть больше 24!" << endl;
			return false;
		}
    		get_modbus_connect(params[0], params[1], params[6]).modbus_read_input_bits((int)params[2], (int)params[3], buff);
		for(int i = 0; i < (int)params[3]; i++)
			php_buff[i] = buff[i];
		return php_buff;
	}
	
	int start_reg = params[2];	

	//Чтение дискретного выхода
	if(params[4] == "DQ" || params[4] == "Q")
	{
		if(params[2] + params[3] > 20)
		{
			Php::out << "Ошибка! Адресс регистра DQ не может быть больше 20!" << endl;
			return false;
		}
		start_reg += 8193;
	}

	//Чтение M
	if(params[4] == "DM" || params[4] == "M")
	{
		if(params[2] + params[3] > 64)
		{
			Php::out << "Ошибка! Адресс регистра DM не может быть больше 64!" << endl;
			return false;
		}
		start_reg += 8257;
	}

	//Чтение V
	if(params[4] == "DV" || params[4] == "V")
	{
		if(params[2] + params[3] > 6800)
		{
			Php::out << "Ошибка! Адресс регистра DM не может быть больше 6800!" << endl;
			return false;
		}
	}

    	get_modbus_connect(params[0], params[1], params[6]).modbus_read_coils(start_reg, (int)params[3], buff);
	for(int i = 0; i < (int)params[3]; i++)
		php_buff[i] = buff[i];
	return php_buff;
		
}

Php::Value modbus_logo8_get_analog_inputs(Php::Parameters &params)
{
	if(params[2] + params[3] > 8)
	{
		Php::out << "Ошибка! Адресс регистра AI не может быть больше 8!" << endl;
		return false;
	}
	uint16_t *buff = new uint16_t[(int)params[3]];
	get_modbus_connect(params[0], params[1], params[6]).modbus_read_input_registers((int)params[2], (int)params[3], buff);
	for(int i = 0; i < (int)params[3]; i++)
		php_buff[i] = buff[i];
	return php_buff;
}

Php::Value modbus_logo8_get_holding_r_by_type(Php::Parameters &params)
{
	int start_reg = params[2];	
	//Чтение VW
	if(params[4] == "VW")
	{
		if(params[2] + params[3] > 425)
		{
			Php::out << "Ошибка! Адресс регистра VM не может быть больше 425!" << endl;
			return false;
		}
	}

	//Чтение AQ
	if(params[4] == "AQ")
	{
		if(params[2] + params[3] > 8)
		{
			Php::out << "Ошибка! Адресс регистра AQ не может быть больше 8!" << endl;
			return false;
		}
		start_reg += 513;
	}

	//Чтение AM
	if(params[4] == "AM")
	{
		if(params[2] + params[3] > 64)
		{
			Php::out << "Ошибка! Адресс регистра AQ не может быть больше 64!" << endl;
			return false;
		}
		start_reg += 529;
	}
	uint16_t *buff = new uint16_t[(int)params[3]];
	get_modbus_connect(params[0], params[1], params[6]).modbus_read_holding_registers(start_reg, (int)params[3], buff);
	for(int i = 0; i < (int)params[3]; i++)
		php_buff[i] = buff[i];
	return php_buff;
}

extern "C" 
{
	PHPCPP_EXPORT void *get_module()
	{
		static Php::Extension extension("modbus_connect","1.0");
		extension.add<modbus_logo8_get_bit_by_type_name>("modbus_logo8_get_bit_by_type_name", {
			Php::ByVal("ip", Php::Type::String),
			Php::ByVal("port", Php::Type::Numeric),
			Php::ByVal("start_reg", Php::Type::Numeric),
			Php::ByVal("count", Php::Type::Numeric),
			Php::ByVal("type_name", Php::Type::String),
			Php::ByVal("slave_id", Php::Type::Numeric)
		});
		extension.add<modbus_logo8_get_analog_inputs>("modbus_logo8_get_analog_inputs", {
			Php::ByVal("ip", Php::Type::String),
			Php::ByVal("port", Php::Type::Numeric),
			Php::ByVal("start_reg", Php::Type::Numeric),
			Php::ByVal("count", Php::Type::Numeric),
			Php::ByVal("slave_id", Php::Type::Numeric)
		});
		extension.add<modbus_logo8_get_holding_r_by_type>("modbus_logo8_get_holding_r_by_type", {
			Php::ByVal("ip", Php::Type::String),
			Php::ByVal("port", Php::Type::Numeric),
			Php::ByVal("start_reg", Php::Type::Numeric),
			Php::ByVal("count", Php::Type::Numeric),
			Php::ByVal("type_name", Php::Type::String),
			Php::ByVal("slave_id", Php::Type::Numeric)
		});
		return extension;
	}
}